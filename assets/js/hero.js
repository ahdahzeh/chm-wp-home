/* ─────────────────────────────────────────────────────────────────
   The gallery hero.

   Geometry, easing and timing are ported verbatim from the approved
   moma-gallery build: room depth, the 1.25 speed ramp on the
   straights, the eased corner traversal, the 96-segment bend, the
   intro move and the frame-rate-independent smoothing are all its
   numbers, not re-derived ones.

   What is not ported is Three.js. That build bundles 616 KB of it to
   draw textured planes bent along a path, which this file already
   does in ~200 lines of WebGL2. Swapping the renderer back is a
   contained change if the dependency is wanted; the numbers below are
   what the room actually looks like.

   Two deliberate differences from the reference, both forced by this
   being a homepage rather than a standalone hero:

   1. The wheel is not captured. The reference sets overflow:hidden on
      the body and preventDefaults every wheel event, which is correct
      when nothing sits below the hero. Here the format bento, Latest
      and the disease clusters do, so a trapped wheel would strand the
      visitor. Drag, touch and the arrow keys drive the room; vertical
      wheel scrolls the page.
   2. The works come from the DOM rather than a JSON fetch, so
      WordPress renders them server-side and every title is crawlable
      and focusable before this file runs.

   The floor plan is a rounded U: a back wall ahead, two side walls
   running forward past the viewer, joined by eased corners. A ring
   cannot work, on a ring a piece that comes closer also swings
   sideways and leaves the frame before it is ever large.
   ───────────────────────────────────────────────────────────────── */

(() => {
  const hero = document.querySelector('[data-hero]');
  if (!hero) return;

  const canvas = hero.querySelector('.hero__canvas');
  const list = hero.querySelector('.hero__works');
  if (!canvas || !list) return;

  const nodes = [...list.querySelectorAll('[data-work]')];
  if (!nodes.length) return;

  const gl = canvas.getContext('webgl2', { antialias: true, alpha: true });
  if (!gl) { hero.dataset.gl = 'off'; return; }

  // ── constants, from the approved build ───────────────────────
  const SEGMENTS = 96;
  const CAM = 2000;
  const SIZE = 0.84;          // piece height as a fraction of base
  const GAP = 0.48;           // spacing between pieces, in means (20% tighter than the reference)
  const LABEL_H = 0.24;       // label height as a fraction of base
  const LABEL_GAP = 0.04;
  // Each work is drawn as a card, surface, inset thumbnail, title and
   // meta composited into one texture, rather than a bare plane with a
   // caption floating under it. Flip to false for the open version.
  const CARDS = true;
  const INTRO_DELAY = 1500;
  const INTRO_RUN = 2400;
  const INTRO_DISTANCE = 1080;
  // The room is centred between the two copy blocks rather than in the
  // hero, because the heading block is taller than the lede, centring
  // on the hero leaves a band of air under the cards and none above.
  // Measured in plan(), so it stays right as the type reflows.
  let dropPx = 0;

  const reduced = matchMedia('(prefers-reduced-motion: reduce)');

  // Cards rest slightly faded and come up to full on hover, with a
  // small lift in scale. The pointer target is the tracked anchor, so
  // this is an ordinary hover on a real link, no raycasting.
  const REST_ALPHA = 0.8;
  const HOVER_SCALE = 1.05;

  // ── shaders ──────────────────────────────────────────────────
  const VERT = `#version 300 es
  precision highp float;
  in vec3 aPos; in vec2 aUV;
  uniform mat4 uProj; uniform float uCam;
  out vec2 vUV;
  void main(){ vUV = aUV; gl_Position = uProj * vec4(aPos.x, aPos.y, aPos.z - uCam, 1.0); }`;

  const FRAG = `#version 300 es
  precision highp float;
  in vec2 vUV; uniform sampler2D uTex; uniform float uAlpha; out vec4 frag;
  void main(){
    vec4 c = texture(uTex, vUV);
    if (c.a < 0.01) discard;
    frag = vec4(c.rgb, c.a * uAlpha);
  }`;

  const compile = (type, src) => {
    const s = gl.createShader(type);
    gl.shaderSource(s, src); gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s));
    return s;
  };

  let prog;
  try {
    prog = gl.createProgram();
    gl.attachShader(prog, compile(gl.VERTEX_SHADER, VERT));
    gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, FRAG));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) throw new Error(gl.getProgramInfoLog(prog));
  } catch (err) {
    console.warn('[chm hero] falling back to the static grid:', err);
    hero.dataset.gl = 'off';
    return;
  }
  gl.useProgram(prog);
  hero.dataset.gl = 'on';

  const A_POS = gl.getAttribLocation(prog, 'aPos');
  const A_UV = gl.getAttribLocation(prog, 'aUV');
  const U_PROJ = gl.getUniformLocation(prog, 'uProj');
  const U_CAM = gl.getUniformLocation(prog, 'uCam');
  const U_TEX = gl.getUniformLocation(prog, 'uTex');
  const U_ALPHA = gl.getUniformLocation(prog, 'uAlpha');

  // No mipmaps and LINEAR on both filters, as the reference does.
  // A piece is never minified far below 1:1 here, and mipmapping
  // visibly softens the works at the back wall.
  function texture(source) {
    const t = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, t);
    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, source);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    return t;
  }

  // Order is shuffled per visit, as it is in the reference, so the
  // room is not the same walk twice.
  const pieces = nodes.map((node) => ({
    node,
    src: node.dataset.thumb,
    title: node.dataset.title || '',
    meta: node.dataset.meta || '',
    size: parseFloat(node.dataset.size) || SIZE,
    aspect: 1.6,
    labelAspect: 1,
    hover: 0,   // target, 0 or 1
    hoverT: 0,  // smoothed
  }));

  for (const piece of pieces) {
    piece.node.addEventListener('pointerenter', () => { piece.hover = 1; });
    piece.node.addEventListener('pointerleave', () => { piece.hover = 0; });
    // A card can slide out from under a stationary cursor, and a link
    // that never gets its leave event would stay lit.
    piece.node.addEventListener('focus', () => { piece.hover = 1; });
    piece.node.addEventListener('blur', () => { piece.hover = 0; });
  }
  for (let i = pieces.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [pieces[i], pieces[j]] = [pieces[j], pieces[i]];
  }

  function readToken(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v ? `hsl(${v})` : fallback;
  }

  // The reference labels carry gallery captions, "The Starry Night"
  // which never need wrapping. CHM session titles run four times that,
  // so the title wraps to at most two lines and the plane is capped.
  // Height is fixed at three rows whatever the title does, so the type
  // stays one size across the room instead of shrinking on long titles.
  const LABEL_MAX = 620;
  const LABEL_TOP = 17.6;
  const LABEL_LINE = 56.32;
  const LABEL_ROWS = 3;

  function labelTexture(piece) {
    const c = document.createElement('canvas');
    const g = c.getContext('2d');
    const titleFont = '400 44px Geist, system-ui, sans-serif';
    const metaFont = '500 32px "Geist Mono", monospace';

    g.font = titleFont;
    const titleLines = [];
    let line = '';
    for (const word of piece.title.split(' ')) {
      const test = line ? line + ' ' + word : word;
      if (g.measureText(test).width > LABEL_MAX && line) {
        titleLines.push(line);
        line = word;
        if (titleLines.length === 2) break;
      } else line = test;
    }
    if (titleLines.length < 2 && line) titleLines.push(line);

    // Anything past two lines is cut rather than shrunk, so the label
    // cannot grow taller than the room allows for it.
    if (titleLines.length === 2) {
      let last = titleLines[1];
      if (g.measureText(last).width > LABEL_MAX) {
        while (last.length > 1 && g.measureText(last + '…').width > LABEL_MAX) last = last.slice(0, -1);
        titleLines[1] = last + '…';
      }
    }

    const rows = [...titleLines.map((t) => [t, titleFont, true])];
    if (piece.meta) rows.push([piece.meta, metaFont, false]);

    let width = 0;
    for (const [text, font] of rows) { g.font = font; width = Math.max(width, g.measureText(text).width); }

    c.width = Math.max(2, Math.ceil(Math.min(width, LABEL_MAX)) * 2);
    c.height = Math.round((LABEL_TOP + LABEL_ROWS * LABEL_LINE + 20) * 2);
    g.scale(2, 2);
    g.textBaseline = 'top';

    rows.forEach(([text, font, isTitle], i) => {
      g.font = font;
      g.fillStyle = isTitle ? readToken('--foreground', '#0d0d0d') : readToken('--muted-foreground', '#4f4f4f');
      g.fillText(text, 0, LABEL_TOP + i * LABEL_LINE);
    });

    piece.labelAspect = c.width / c.height;
    return c;
  }

  // ── the floor plan ───────────────────────────────────────────
  let path = null, loopLength = 1, base = 0;

  function plan(w, h) {
    const above = hero.querySelector('.hero__copy--top');
    const below = hero.querySelector('.hero__copy--bottom');
    const bandTop = above ? above.offsetTop + above.offsetHeight : 0;
    const bandBottom = below ? below.offsetTop : h;
    const band = Math.max(140, bandBottom - bandTop);
    dropPx = (bandTop + bandBottom) / 2 - h / 2;

    // Cards are sized to the band between the copy blocks rather than
    // to the hero, so growing the heading takes room from the cards
    // instead of sliding them underneath it.
    // 1.02 rather than a strict fit: the cards that exceed the band are
    // the near ones on the side walls, and those sit at the frame edges
    // where the centred heading is not.
    base = Math.min(0.64 * h, 560, (band * 1.02) / SIZE);

    let total = 0;
    for (const p of pieces) {
      p.h = base * p.size;
      p.w = p.h * p.aspect;
      p.lh = LABEL_H * base;
      p.lw = p.lh * p.labelAspect;
      p.ly = -p.h / 2 - LABEL_GAP * base - p.lh / 2;
      total += p.w;
    }

    const mean = total / pieces.length;
    const narrow = w < 700;
    const width = mean * (narrow ? 1.248 : 2.304);
    const depth = mean * (narrow ? 3.5 : 4.5);
    const front = Math.max(0, Math.min(2000 * (1 - width / w) + 200, 1200));
    const radius = Math.max(0, Math.min(0.75 * mean, width / 2, depth + front));

    path = {
      width, depth, front, radius,
      side: (depth + front - radius) / 1.25,
      corner: (Math.PI * radius) / 2.25,
      back: width - 2 * radius,
    };
    path.length = 2 * path.side + 2 * path.corner + path.back;

    let cursor = 0;
    for (const p of pieces) { p.center = cursor + p.w / 2; cursor += p.w + GAP * mean; }
    loopLength = Math.max(cursor, path.length + 2 * mean);
    dirty = true;
  }

  // Eased corner traversal. A constant-radius sweep makes a piece
  // cross the corner at a steady rate, which reads as a turntable;
  // this accelerates into the turn and out of it.
  function cornerAngle(f, start, end) {
    return (Math.PI * (start * f + (end - start) * (f ** 3 - f ** 4 / 2))) / (start + end);
  }

  function point(distance) {
    const { width, depth, front, radius, side, corner, back } = path;
    let d = Math.max(0, Math.min(distance, path.length));

    if (d <= side) return [-width / 2, front - d * 1.25];
    d -= side;
    if (d <= corner) {
      const a = cornerAngle(d / corner, 1.25, 1);
      return [-width / 2 + radius - radius * Math.cos(a), -depth + radius - radius * Math.sin(a)];
    }
    d -= corner;
    if (d <= back) return [-width / 2 + radius + d, -depth];
    d -= back;
    if (d <= corner) {
      const a = cornerAngle(d / corner, 1, 1.25);
      return [width / 2 - radius + radius * Math.sin(a), -depth + radius - radius * Math.cos(a)];
    }
    d -= corner;
    return [width / 2, -depth + radius + d * 1.25];
  }

  // ── meshes ───────────────────────────────────────────────────
  const FLOATS = SEGMENTS * 6 * 5;
  const vao = gl.createVertexArray();
  const buf = gl.createBuffer();
  gl.bindVertexArray(vao);
  gl.bindBuffer(gl.ARRAY_BUFFER, buf);
  gl.bufferData(gl.ARRAY_BUFFER, FLOATS * 4, gl.DYNAMIC_DRAW);
  gl.enableVertexAttribArray(A_POS); gl.vertexAttribPointer(A_POS, 3, gl.FLOAT, false, 20, 0);
  gl.enableVertexAttribArray(A_UV); gl.vertexAttribPointer(A_UV, 2, gl.FLOAT, false, 20, 12);
  gl.bindVertexArray(null);
  const scratch = new Float32Array(FLOATS);

  // Clipped to the visible span rather than drawn whole, and the UV
  // is remapped across the clip, so a piece running off the end of
  // the path is cropped instead of squashed.
  function bend(center, width, height, y) {
    const left = center - width / 2;
    const start = Math.max(left, 0);
    const end = Math.min(left + width, path.length);
    if (end <= start) return 0;

    let n = 0;
    for (let i = 0; i < SEGMENTS; i++) {
      const d0 = start + ((end - start) * i) / SEGMENTS;
      const d1 = start + ((end - start) * (i + 1)) / SEGMENTS;
      const u0 = (d0 - left) / width, u1 = (d1 - left) / width;
      const [x0, z0] = point(d0), [x1, z1] = point(d1);
      const top = y + height / 2, bottom = y - height / 2;
      const push = (x, yy, z, u, v) => {
        scratch[n++] = x; scratch[n++] = yy; scratch[n++] = z; scratch[n++] = u; scratch[n++] = v;
      };
      push(x0, top, z0, u0, 0); push(x1, top, z1, u1, 0); push(x0, bottom, z0, u0, 1);
      push(x1, top, z1, u1, 0); push(x1, bottom, z1, u1, 1); push(x0, bottom, z0, u0, 1);
    }
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferSubData(gl.ARRAY_BUFFER, 0, scratch, 0, n);
    return SEGMENTS * 6;
  }

  const proj = new Float32Array(16);

  function resize() {
    const dpr = Math.min(2, window.devicePixelRatio || 1);
    const w = hero.clientWidth, h = hero.clientHeight;
    if (!w || !h) return;
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    gl.viewport(0, 0, canvas.width, canvas.height);
    plan(w, h);

    // fov = 2·atan(h/4000), the reference's camera. One world unit is
    // one CSS pixel at the back wall, so the window decides how much
    // of the room is in shot and never how big anything is.
    const fov = 2 * Math.atan(h / (2 * CAM));
    const f = 1 / Math.tan(fov / 2);
    const near = 1, far = 20000;
    proj.fill(0);
    proj[0] = f / (w / h); proj[5] = f;
    proj[10] = (far + near) / (near - far); proj[11] = -1;
    proj[14] = (2 * far * near) / (near - far);
  }

  // ── motion ───────────────────────────────────────────────────
  // The room never stops and nothing interrupts it. The opening move
  // is a deceleration into the cruise rather than a stop, so there is
  // no seam where the entrance hands over to the loop.
  const PEAK = 1350;   // units per second at the start of the entrance
  const CRUISE = 130;  // units per second, forever after
  let current = 0;
  let lastFrame = performance.now(), started = lastFrame;
  let running = true;

  // Speed decays from the entrance peak to the cruise across the
  // intro. (1-u)² lands on CRUISE with matching slope, so the handover
  // is invisible.
  function speedAt(now) {
    if (reduced.matches) return 0;
    const t = now - started - INTRO_DELAY;
    if (t <= 0) return 0;
    const u = Math.min(1, t / INTRO_RUN);
    return CRUISE + (PEAK - CRUISE) * (1 - u) ** 2;
  }

  // Pause off-screen and in background tabs. A carousel nobody is
  // looking at should not hold a GPU awake.
  document.addEventListener('visibilitychange', () => {
    running = !document.hidden;
    if (running) { lastFrame = performance.now(); requestAnimationFrame(render); }
  });

  let onScreen = true;
  new IntersectionObserver((entries) => {
    onScreen = entries[0].isIntersecting;
    if (onScreen && running) { lastFrame = performance.now(); requestAnimationFrame(render); }
  }, { threshold: 0 }).observe(hero);

  // ── screen-space link tracking ───────────────────────────────
  // Each piece already has a real <a> in the markup. Rather than
  // raycasting the canvas, the piece's corners are projected back to
  // CSS pixels each frame and the anchor is parked over them. Clicks,
  // middle-click, right-click and keyboard focus then all behave like
  // the ordinary links they are.
  function project(x, y, z, w, h) {
    const clipW = CAM - z;
    if (clipW <= 1) return null;
    const ndcX = (proj[0] * x) / clipW;
    const ndcY = (proj[5] * y) / clipW;
    return [(ndcX * 0.5 + 0.5) * w, (1 - (ndcY * 0.5 + 0.5)) * h];
  }

  function trackLink(piece, center, w, h) {
    const node = piece.node;
    // The box follows the hover scale, or the pointer target drifts off
    // the card it belongs to while the card is lifted.
    const k = 1 + (HOVER_SCALE - 1) * piece.hoverT;
    const pw = piece.w * k, ph = piece.h * k;
    const left = center - pw / 2;
    const start = Math.max(left, 0);
    const end = Math.min(left + pw, path.length);
    if (end <= start) { node.style.display = 'none'; return; }

    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity, depth = 0;
    // Five samples across the span catch the bend round a corner; the
    // bounding box of a curved piece is not its two end points.
    for (let i = 0; i <= 4; i++) {
      const d = start + ((end - start) * i) / 4;
      const [x, z] = point(d);
      depth += z;
      const drop = -dropPx;
      for (const yy of [ph / 2 + drop, -ph / 2 + drop]) {
        const p = project(x, yy, z, w, h);
        if (!p) { node.style.display = 'none'; return; }
        if (p[0] < minX) minX = p[0];
        if (p[0] > maxX) maxX = p[0];
        if (p[1] < minY) minY = p[1];
        if (p[1] > maxY) maxY = p[1];
      }
    }

    node.style.display = '';
    node.style.left = `${minX}px`;
    node.style.top = `${minY}px`;
    node.style.width = `${Math.max(0, maxX - minX)}px`;
    node.style.height = `${Math.max(0, maxY - minY)}px`;
    // Nearer pieces take the click where two overlap at a corner.
    // depth is a sum of z values, all negative and more negative the
    // further back a piece sits, so this has to be scaled and floored
    // into a positive band, a negative z-index would drop the link
    // behind its own stacking context.
    node.style.zIndex = String(Math.max(1, Math.round(1000 + depth / 25)));
  }

  function render(now) {
    if (!running || !onScreen) return;

    const dt = Math.min(now - lastFrame, 80);
    lastFrame = now;
    current += (speedAt(now) * dt) / 1000;

    const w = hero.clientWidth, h = hero.clientHeight;

    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
    gl.enable(gl.DEPTH_TEST);
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
    gl.uniformMatrix4fv(U_PROJ, false, proj);
    gl.uniform1f(U_CAM, CAM);
    gl.uniform1i(U_TEX, 0);
    gl.activeTexture(gl.TEXTURE0);
    gl.bindVertexArray(vao);

    // Placed first, then drawn far to near. With the cards no longer
    // fully opaque, draw order decides whether a near card blends over
    // the one behind it or punches a hole in it.
    const order = [];
    for (const p of pieces) {
      if (!p.tex) continue;
      let center = ((p.center - current) % loopLength + loopLength) % loopLength;
      // A piece past the end of the path may still have its tail in
      // shot one loop back.
      if (center - p.w / 2 >= path.length && center - loopLength + p.w / 2 > 0) center -= loopLength;

      p.hoverT += (p.hover - p.hoverT) * (1 - Math.pow(0.82, dt / (1000 / 60)));
      if (Math.abs(p.hover - p.hoverT) < 0.001) p.hoverT = p.hover;

      const [, z] = point(Math.max(0, Math.min(center, path.length)));
      order.push({ p, center, z });
    }
    order.sort((a, b) => a.z - b.z);

    const drop = -dropPx;
    for (const { p, center } of order) {
      gl.uniform1f(U_ALPHA, REST_ALPHA + (1 - REST_ALPHA) * p.hoverT);
      const k = reduced.matches ? 1 : 1 + (HOVER_SCALE - 1) * p.hoverT;

      gl.bindTexture(gl.TEXTURE_2D, p.tex);
      let n = bend(center, p.w * k, p.h * k, drop);
      if (n) gl.drawArrays(gl.TRIANGLES, 0, n);

      if (p.labelTex && !CARDS) {
        gl.bindTexture(gl.TEXTURE_2D, p.labelTex);
        n = bend(center - p.w / 2 + p.lw / 2, p.lw, p.lh, p.ly + drop);
        if (n) gl.drawArrays(gl.TRIANGLES, 0, n);
      }

      trackLink(p, center, w, h);
    }

    gl.bindVertexArray(null);
    requestAnimationFrame(render);
  }

  // ── load ─────────────────────────────────────────────────────
  // ── the card ─────────────────────────────────────────────────
  // Composited in a 2D canvas and uploaded as one texture, so a card
  // is still a single quad in the room. The surface is the --card
  // token rather than white: on a white page a white card has no edge,
  // and the design system already puts cards a step down from the page.
  function roundRect(g, x, y, w, h, r) {
    if (g.roundRect) { g.beginPath(); g.roundRect(x, y, w, h, r); return; }
    g.beginPath();
    g.moveTo(x + r, y);
    g.arcTo(x + w, y, x + w, y + h, r);
    g.arcTo(x + w, y + h, x, y + h, r);
    g.arcTo(x, y + h, x, y, r);
    g.arcTo(x, y, x + w, y, r);
    g.closePath();
  }

  const CARD_W = 340, CARD_PAD = 12, CARD_GAP = 10;
  const CARD_TITLE = 15, CARD_LINE = 20, CARD_META = 14;

  function cardTexture(piece, img) {
    const inner = CARD_W - CARD_PAD * 2;
    const imgH = Math.round(inner / (img.width / img.height || 1.6));

    const probe = document.createElement('canvas').getContext('2d');
    probe.font = `400 ${CARD_TITLE}px Geist, system-ui, sans-serif`;
    const lines = [];
    let line = '';
    for (const word of piece.title.split(' ')) {
      const test = line ? line + ' ' + word : word;
      if (probe.measureText(test).width > inner && line) {
        lines.push(line); line = word;
        if (lines.length === 2) break;
      } else line = test;
    }
    if (lines.length < 2 && line) lines.push(line);
    if (lines.length === 2 && probe.measureText(lines[1]).width > inner) {
      let last = lines[1];
      while (last.length > 1 && probe.measureText(last + '…').width > inner) last = last.slice(0, -1);
      lines[1] = last + '…';
    }

    // Height is fixed at two title lines whatever the title runs to, so
    // every card in the room is the same shape.
    const textH = 2 * CARD_LINE + 6 + CARD_META;
    const CH = CARD_PAD + imgH + CARD_GAP + textH + CARD_PAD;

    const SC = 2;
    const c = document.createElement('canvas');
    c.width = CARD_W * SC; c.height = Math.round(CH * SC);
    const g = c.getContext('2d');
    g.scale(SC, SC);

    g.fillStyle = readToken('--card', '#f5f5f5');
    roundRect(g, 0, 0, CARD_W, CH, 14); g.fill();

    g.save();
    roundRect(g, CARD_PAD, CARD_PAD, inner, imgH, 8); g.clip();
    g.drawImage(img, CARD_PAD, CARD_PAD, inner, imgH);
    g.restore();

    g.textBaseline = 'top';
    g.fillStyle = readToken('--foreground', '#0d0d0d');
    g.font = `400 ${CARD_TITLE}px Geist, system-ui, sans-serif`;
    lines.forEach((l, i) => g.fillText(l, CARD_PAD, CARD_PAD + imgH + CARD_GAP + i * CARD_LINE));

    g.fillStyle = readToken('--muted-foreground', '#4f4f4f');
    g.font = '500 11px "Geist Mono", monospace';
    g.letterSpacing = '0.06em';
    g.fillText(piece.meta.toUpperCase(), CARD_PAD, CARD_PAD + imgH + CARD_GAP + 2 * CARD_LINE + 6);

    piece.aspect = CARD_W / CH;
    return c;
  }

  function mount(piece) {
    return new Promise((resolve) => {
      const img = new Image();
      img.decoding = 'async';
      img.onload = () => {
        if (CARDS) {
          piece.tex = texture(cardTexture(piece, img));
        } else {
          piece.aspect = img.width / img.height;
          piece.tex = texture(img);
        }
        resize();
        resolve();
      };
      img.onerror = () => {
        console.warn('[chm hero] thumbnail failed, piece skipped:', piece.src);
        resolve();
      };
      img.src = piece.src;
    });
  }

  function start() {
    if (!CARDS) for (const p of pieces) p.labelTex = texture(labelTexture(p));
    resize();
    new ResizeObserver(resize).observe(hero);
    started = lastFrame = performance.now();
    requestAnimationFrame(render);
  }

  Promise.all(pieces.map(mount)).then(() => {
    if (!pieces.some((p) => p.tex)) { hero.dataset.gl = 'off'; return; }
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(start);
    else start();
  });
})();

/* ── the nav disclosure ───────────────────────────────────────── */
(() => {
  const bar = document.querySelector('[data-site-bar]');
  const toggle = bar && bar.querySelector('.site-bar__toggle');
  if (!bar || !toggle) return;
  toggle.addEventListener('click', () => {
    const open = bar.dataset.open === 'true';
    bar.dataset.open = open ? 'false' : 'true';
    toggle.setAttribute('aria-expanded', String(!open));
  });
})();

/* ── Latest tabs ──────────────────────────────────────────────────
   APG tabs: roving tabindex, arrow keys wrap, Home and End jump to
   the ends.
   ─────────────────────────────────────────────────────────────── */
(() => {
  for (const group of document.querySelectorAll('[data-tabs]')) {
    const tabs = [...group.querySelectorAll('[role="tab"]')];
    const panels = [...group.querySelectorAll('[role="tabpanel"]')];
    if (!tabs.length) continue;

    const select = (i, focus = true) => {
      tabs.forEach((tab, n) => {
        const on = n === i;
        tab.setAttribute('aria-selected', String(on));
        tab.tabIndex = on ? 0 : -1;
        if (panels[n]) panels[n].hidden = !on;
      });
      if (focus) tabs[i].focus();
    };

    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => select(i, false));
      tab.addEventListener('keydown', (e) => {
        const last = tabs.length - 1;
        let next = null;
        if (e.key === 'ArrowRight') next = i === last ? 0 : i + 1;
        else if (e.key === 'ArrowLeft') next = i === 0 ? last : i - 1;
        else if (e.key === 'Home') next = 0;
        else if (e.key === 'End') next = last;
        if (next === null) return;
        e.preventDefault();
        select(next);
      });
    });

    select(Math.max(0, tabs.findIndex((t) => t.getAttribute('aria-selected') === 'true')), false);
  }
})();

/* ── the field behind the room ────────────────────────────────────
   The CHM dot motif, drawn behind the gallery and faded out through
   the middle band so it never competes with the thumbnails. Same
   square motes as the disease clusters, so the two read as one
   system rather than two effects.
   ─────────────────────────────────────────────────────────────── */
(() => {
  const cv = document.querySelector('[data-hero] .hero__field');
  if (!cv) return;
  const ctx = cv.getContext('2d');
  if (!ctx) return;

  const host = cv.parentElement;
  const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let w = 0, h = 0, motes = [], frame = 0, onScreen = true;

  const seed = () => {
    // Density scales with area so a wide monitor does not thin out.
    const n = Math.round(Math.min(1600, (w * h) / 950));
    motes = Array.from({ length: n }, () => ({
      x: Math.random(),
      y: Math.random(),
      // Mostly drifting right, at a spread of speeds, so the field has
      // depth of its own without competing with the room's direction.
      vx: 0.004 + Math.random() * 0.012,
      vy: (Math.random() - 0.5) * 0.004,
      z: Math.random() < 0.3 ? 2.4 : 1.6,
      o: 0.1 + Math.random() * 0.34,
    }));
  };

  const fit = () => {
    const r = host.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    cv.width = Math.max(1, Math.round(r.width * dpr));
    cv.height = Math.max(1, Math.round(r.height * dpr));
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    w = r.width; h = r.height;
    seed();
  };

  const ink = () => {
    const t = getComputedStyle(document.documentElement).getPropertyValue('--foreground').trim();
    return t ? `hsl(${t})` : '#0d0d0d';
  };

  let last = performance.now();
  const draw = (now) => {
    const dt = Math.min((now || performance.now()) - last, 80);
    last = now || last;
    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = ink();
    for (const m of motes) {
      if (!still) {
        m.x += (m.vx * dt) / 100;
        m.y += (m.vy * dt) / 100;
        if (m.x > 1.02) m.x = -0.02;
        if (m.y > 1.02) m.y = -0.02;
        if (m.y < -0.02) m.y = 1.02;
      }
      ctx.globalAlpha = m.o;
      ctx.fillRect(m.x * w, m.y * h, m.z, m.z);
    }
    ctx.globalAlpha = 1;
    if (!still && onScreen) frame = requestAnimationFrame(draw);
  };

  fit();
  draw();

  new IntersectionObserver(([e]) => {
    if (e.isIntersecting === onScreen) return;
    onScreen = e.isIntersecting;
    if (onScreen) { last = performance.now(); draw(); }
    else cancelAnimationFrame(frame);
  }, { rootMargin: '120px' }).observe(cv);

  let pending;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(pending);
    pending = requestAnimationFrame(() => { fit(); if (still) draw(); });
  });
})();

/* ── the audio cut ────────────────────────────────────────────────
   Flowing line traces rather than a bar meter: forty thin curves,
   each a sum of two sines under a travelling envelope, so the field
   gathers into packets and thins out between them. Colour runs amber
   through coral to Knowledge Blue across the stack, which is what
   gives it depth, a single hue reads flat at this line weight.
   ─────────────────────────────────────────────────────────────── */
(() => {
  const cv = document.querySelector('[data-wave]');
  if (!cv) return;
  const ctx = cv.getContext('2d');
  if (!ctx) return;

  const LINES = 42;
  const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let w = 0, h = 0, frame = 0, onScreen = true, phase = 0;

  const fit = () => {
    const r = cv.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    cv.width = Math.max(1, Math.round(r.width * dpr));
    cv.height = Math.max(1, Math.round(r.height * dpr));
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    w = r.width; h = r.height;
  };
  fit();

  const read = (name, fallback) => {
    const t = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return t ? t.split(/\s+/) : fallback;
  };

  // Two families rather than one ramp. Interpolating straight from
  // amber (37) to coral (359) walks the hue wheel the long way round
  // and comes out magenta, which is what the first pass did. Warm and
  // cool are interpolated separately and interleaved, which is also
  // what the reference actually shows, orange and blue traces
  // crossing each other, not a gradient between them.
  const WARM = [[37, 91, 55], [14, 74, 52]];   // amber → rust
  const COOL = [[193, 63, 49], [196, 66, 32]]; // knowledge blue → deep expertise

  function traceColour(i, t) {
    // A slow sine clumps the families instead of hard-alternating them.
    const cool = Math.sin(i * 0.62) > 0.1;
    const [a, b] = cool ? COOL : WARM;
    const u = (Math.sin(i * 1.7) + 1) / 2;
    const m = (k) => a[k] + (b[k] - a[k]) * u;
    return `hsl(${m(0).toFixed(1)} ${m(1).toFixed(1)}% ${m(2).toFixed(1)}%)`;
  }

  const draw = () => {
    ctx.clearRect(0, 0, w, h);
    ctx.lineWidth = 0.7;
    ctx.globalAlpha = 0.5;

    for (let i = 0; i < LINES; i++) {
      const t = i / (LINES - 1);
      ctx.beginPath();
      for (let x = 0; x <= w; x += 2) {
        const u = x / w;
        // Envelope: three travelling packets across the width.
        const env = Math.pow(Math.abs(Math.sin(u * Math.PI * 3.1 + phase * 0.35)), 1.7) * 0.86 + 0.14;
        const y = h / 2
          + Math.sin(u * 15 + i * 0.19 + phase) * h * 0.2 * env
          + Math.sin(u * 27 - i * 0.31 - phase * 1.4) * h * 0.13 * env
          + (t - 0.5) * h * 0.5 * env;
        if (x === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
      }
      ctx.strokeStyle = traceColour(i, t);
      ctx.stroke();
    }
    ctx.globalAlpha = 1;

    if (!still && onScreen) { phase += 0.006; frame = requestAnimationFrame(draw); }
  };
  draw();

  new IntersectionObserver(([e]) => {
    if (e.isIntersecting === onScreen) return;
    onScreen = e.isIntersecting;
    if (onScreen) draw(); else cancelAnimationFrame(frame);
  }, { rootMargin: '120px' }).observe(cv);

  let pending;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(pending);
    pending = requestAnimationFrame(() => { fit(); if (still) draw(); });
  });
})();

/* ── the pipeline ─────────────────────────────────────────────────
   One stream in, three formats out. The operational claim the
   platform card makes, drawn rather than described: a single grey
   inflow splits at a junction and leaves down three coloured lanes.
   Ported from the app's Pipeline component.
   ─────────────────────────────────────────────────────────────── */
(() => {
  const cv = document.querySelector('[data-pipeline]');
  if (!cv) return;
  const ctx = cv.getContext('2d');
  if (!ctx) return;

  const LANES = [-1, 0, 1];
  const dots = Array.from({ length: 120 }, (_, i) => ({
    t: Math.random(),
    lane: LANES[i % 3],
    sp: 0.0022 + Math.random() * 0.003,
    o: 0.3 + Math.random() * 0.7,
  }));

  let w = 0, h = 0, frame = 0, onScreen = true;
  const still = !matchMedia('(prefers-reduced-motion: no-preference)').matches;

  const fit = () => {
    const r = cv.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    cv.width = Math.max(1, Math.round(r.width * dpr));
    cv.height = Math.max(1, Math.round(r.height * dpr));
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    w = r.width; h = r.height;
  };
  fit();

  const read = (name, fallback) => {
    const t = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return t ? `hsl(${t})` : fallback;
  };

  const draw = () => {
    ctx.clearRect(0, 0, w, h);
    // Brand hues, not the app's spectrum: the three lanes are
    // Knowledge Blue, Amber and Deep Expertise.
    const out = [
      read('--deep-expertise', 'hsl(196 66% 23%)'),
      read('--knowledge-blue', 'hsl(193 63% 49%)'),
      read('--amber', 'hsl(37 91% 55%)'),
    ];
    const grey = read('--faint', 'hsl(216 15% 54%)');

    const inX = w * 0.06, splitX = w * 0.4, outX = w * 0.97;
    const c1 = splitX + 40, c2 = outX - 56;

    ctx.globalAlpha = 0.16;
    ctx.strokeStyle = grey;
    ctx.lineWidth = 1;
    for (const lane of LANES) {
      const y2 = h / 2 + lane * h * 0.34;
      ctx.beginPath();
      ctx.moveTo(inX, h / 2);
      ctx.lineTo(splitX, h / 2);
      ctx.bezierCurveTo(c1, h / 2, c2, y2, outX, y2);
      ctx.stroke();
    }

    for (const d of dots) {
      if (!still) { d.t += d.sp; if (d.t > 1) d.t = 0; }
      let px, py;
      if (d.t < 0.4) {
        // The shared inflow: everything is one recording up to here.
        px = inX + (splitX - inX) * (d.t / 0.4);
        py = h / 2;
      } else {
        const u = (d.t - 0.4) / 0.6;
        const y2 = h / 2 + d.lane * h * 0.34;
        const m = 1 - u;
        px = m * m * m * splitX + 3 * m * m * u * c1 + 3 * m * u * u * c2 + u * u * u * outX;
        py = m * m * m * (h / 2) + 3 * m * m * u * (h / 2) + 3 * m * u * u * y2 + u * u * u * y2;
      }
      ctx.globalAlpha = d.o * (d.t < 0.4 ? 0.5 : 0.95);
      ctx.fillStyle = d.t < 0.4 ? grey : out[d.lane + 1];
      ctx.beginPath();
      ctx.arc(px, py, d.t < 0.4 ? 1.3 : 1.8, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.globalAlpha = 1;

    if (!still && onScreen) frame = requestAnimationFrame(draw);
  };
  draw();

  new IntersectionObserver(([e]) => {
    if (e.isIntersecting === onScreen) return;
    onScreen = e.isIntersecting;
    if (onScreen) draw(); else cancelAnimationFrame(frame);
  }, { rootMargin: '120px' }).observe(cv);

  let pending;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(pending);
    pending = requestAnimationFrame(() => { fit(); if (still) draw(); });
  });
})();
