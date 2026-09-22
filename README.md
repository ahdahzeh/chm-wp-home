# CHM homepage. WordPress theme

The public homepage, rendered server-side from the posts already on
`communityhealth.media`. Nine sections, following the Figma frame
`643:6503` ("01 · Home v2, C · Gallery"): hero → content first →
everything new → disease states → this moment → in conversation →
podcast network, then the closing band, CTA and footer on one
continuous slate, which lives in `footer.php` so every template
carries it, not only the homepage.

## Brand

Colour and type come from the CHM brand guide (`uOCFBJbTb89meNBmoeeAld`,
page `275:2`), with the usage ratios it sets, those ratios are what
keep the accent an accent:

| Token | Hex | Role | Ratio |
|---|---|---|---|
| Base White | `#F2F4F8` | ground, cards, surfaces | ~70% |
| Precision Ink | `#22303C` | all body and heading copy | ~15% |
| Knowledge Blue | `#2FA9CC` | logo, primary CTA, links, active | ~8% |
| Deep Expertise | `#144C60` | heroes, footers, dark surfaces | ~5% |
| Amber | `#F5A524` | payments, emphasis, live, warmth | ~2% |

Discovery Gray is a tint utility on light grounds only, dividers,
disabled, placeholders. It never sits beside the logo, fills a brand
card, or carries meaning; on dark surfaces tints derive from Deep
Expertise instead. Success, warning, error and info sit outside the
brand palette and never replace a brand colour.

Chillax is the display face, Geist carries body and Geist Mono the
data. The seven disease hues sit outside the five-colour core and tint
the cluster cards only.

## Why this shape

WordPress already holds the content: 525 posts, 44 categories, 260 tags,
90 series, with `cht-webhook.php` installed as an mu-plugin pushing
changes to ContentHub. Rendering the public pages *from* WordPress
removes a layer rather than adding one, and it fixes the thing the React
homepage could not: every title, every disease area and every format now
exists in the HTML before any JavaScript runs.

The design system is a straight port. `assets/css/tokens.css` is the
same set of custom properties the app ships in `src/index.css`, copied
across unchanged, so the two properties cannot drift. There is no build
step and no Tailwind.

## Local install

A working WordPress lives at `~/Documents/chm-wp-local`: core 7.1.1 on
SQLite (no MySQL), with the theme symlinked in and seeded content.

```bash
cd ~/Documents/chm-wp-local && php -S 0.0.0.0:8899 -t . router.php
```

`router.php` exists only for PHP's built-in server. `php -S` serves
static files itself until you hand it a router; with WordPress's front
controller as the router every .css and .js request went through
WordPress and came back as a redirect to an HTML page, which the
browser then cached as a permanent 301. Apache and nginx need none of
this. If assets ever look stale after a change, bump `CHM_VERSION`:
that changes the query on every asset URL and steps around a cached
redirect.

Permalinks are set so the nav resolves: `category_base` is `catalog`,
so a disease state lives at `/catalog/breast`, and `/catalog` is the
posts page.

Admin: `chm` / `chm-local-dev` at `/wp-admin`. Local only.

## Install on a server

1. Copy this directory into `wp-content/themes/chm-home/`.
2. Activate **CHM Homepage** in Appearance → Themes.
3. Appearance → Menus: create a menu and assign it to the **Primary**
   location. Five items, in this order: Latest, Videos, Podcasts,
   Editorial, Disease states. Optional, with no menu assigned those
   five render anyway, from `chm_default_nav()`.
4. Settings → Reading: set the front page to display your latest posts,
   or a static page. `front-page.php` takes over either way.

Nothing else is required. The homepage will render with whatever posts
exist; sections with no matching posts say so rather than showing
placeholder content.

## What to check on the install before it goes live

Three assumptions were made from the codebase rather than from wp-admin,
because I did not have access. Each is a one-line fix if wrong.

| Assumption | Where | If wrong |
|---|---|---|
| Formats live in a `format` taxonomy | `functions.php`, `chm_recent_posts()` and `chm_meta_line()` | Change the `taxonomy` key to whatever the install uses |
| Disease areas are `category` terms with slugs `breast`, `weight-loss`, `lung`, `hematology`, `gi`, `gu`, `gynecology` | `inc/data.php`, `chm_disease_areas()` | Correct the slugs. They are pinned to the catalog URLs, so check against wp-admin rather than guessing |
| Duration is in a `duration` post meta key | `functions.php`, `chm_duration()` | Change the key, or the line falls back to the post date, which is harmless |
| The channel chip reads a `channel` post meta key | `functions.php`, `chm_card_tags()` | Change the key, or the card shows one chip instead of two |
| This Moment in Medicine is a `series` or `category` term slugged `this-moment-in-medicine` | `functions.php`, `chm_moments()` | Correct the slug. With no match the row falls back to recent posts, which is wrong but not broken |

## Files

```
style.css              theme header only
index.php              fallback listing
archive.php            the library, and every term archive under it
single.php             one session, clip, episode or perspective
page.php               About, For HCPs, Contact, Privacy, Terms
search.php             search results
404.php                not found
functions.php          setup, enqueues, and the queries the page runs
inc/mark.php           the CHM chevron, in one place rather than eight
inc/data.php           disease states, shows, clinicians, nav fallback
inc/page-parts.php     header band, post card, card grid, area chips
header.php             document head and the site bar
front-page.php         the homepage, seven sections
footer.php             the closing band: CTA and footer on one slate
assets/css/tokens.css  design system, ported from the app's index.css
assets/css/home.css    homepage sections
assets/js/hero.js      the WebGL gallery, nav disclosure, APG chips
assets/img/            seven anatomy fields and the texture plates
preview/index.html     the same markup as static HTML, for checking
                       design changes without a WordPress install
preview/wp-render.html the PHP rendered through the stub, for diffing
tools/                 the stub; WordPress never loads it
```

`preview/index.html` and the PHP are in sync: same sections, same
markup, same order. Change one and change the other, or the preview
stops being evidence of anything. `tools/render-preview.php` renders
the PHP outside WordPress so the two can be diffed directly.

## What the hero does

The twelve works are a real `<ul>` of links rendered by
`chm_hero_works()`. The canvas draws on top of that list. Delete
`hero.js` and the page still works: the list becomes a responsive grid.
That is also what happens on a browser without WebGL2, and what a
crawler sees.

The floor plan is a rounded U, a back wall ahead, two side walls
running forward past the viewer, joined by eased corners. A ring does
not work here: on a ring a work that comes closer also swings sideways
and leaves the frame before it is ever large.

### Cards

Each work is composited in a 2D canvas, `--card` surface, inset
thumbnail, title and meta, and uploaded as one texture, so a card is
still a single quad in the room. The surface is the card token rather
than white because on a white page a white card has no edge, and the
design system already puts cards a step below the page. `CARDS = false`
in `hero.js` reverts to bare planes with a caption floating under them.

### Shows

Two columns, each show on its own deep ground (`--show-navy`,
`--show-teal`, `--show-blue`, `--show-rust` in `tokens.css`) so the
network reads as four shows rather than four instances of one card.
These are grounds carrying white text, not spectrum hues, which is why
they sit far darker than the bright set.

### Hover

Cards rest at `REST_ALPHA` 0.8 and ease to full opacity with a 1.05
scale when hovered or focused, smoothed on the same `.82^(dt/16.7)`
curve as the room. The pointer target is the tracked anchor, so this is
an ordinary hover on a real link rather than raycasting the canvas, and
keyboard focus lights a card the same way. The tracked box follows the
scale, or the target would drift off a lifted card.

Because the cards are no longer opaque, they are now depth-sorted and
drawn far to near each frame, otherwise a near card punches a hole in
the one behind it instead of blending over it. The scale lift is
dropped under `prefers-reduced-motion`; the opacity change stays.

### Motion

The room never stops and nothing interrupts it: there is no drag, and
the wheel is left to the page. The opening move decelerates into a
constant cruise rather than stopping, so there is no seam where the
entrance hands over to the loop.

Each piece's anchor is projected back to CSS pixels every frame and
parked over the work it belongs to, so clicking a moving piece opens
that session. They are ordinary links, which means middle-click,
right-click and keyboard focus all behave. The page pauses the loop
when the hero scrolls out of view or the tab is hidden.

### Ported from `moma-gallery`

The geometry, easing and timing are that build's numbers, not
re-derived ones:

| | Value |
|---|---|
| Room depth | `mean × 4.5` desktop, `× 3.5` under 700px |
| Room width | `mean × 2.304` desktop, `× 1.248` under 700px |
| Front run | `clamp(2000 × (1 − width/w) + 200, 0, 1200)` |
| Corner radius | `min(0.75 × mean, width/2, depth + front)` |
| Straights | `side = (depth + front − radius) / 1.25`, distance `× 1.25` |
| Corners | `π × radius / 2.25`, eased on `f³ − f⁴/2` |
| Subdivisions | 96 per piece |
| Piece height | `0.76 × base`, `base = min(0.6h, 560)` |
| Spacing | `piece width + 0.48 × mean` (20% tighter than the reference) |
| Opening | hold 1500ms, then 1080 units over 2400ms on `1 − (1−t)³` |
| Smoothing | `1 − 0.82^(dt/16.7)`, velocity decay `e^(−dt/240)` |
| Canvas rise | `translateY(100% → −64px)`, 1.9s, delayed 1.5s |

Three deliberate differences, all forced by this being a homepage
rather than a standalone hero:

1. **No Three.js.** That build bundles 616 KB of it to draw textured
   planes bent along a path, which `hero.js` does directly in WebGL2.
   Swapping the renderer back is contained if the dependency is wanted.
2. **The wheel is not captured.** The reference sets `overflow:hidden`
   on the body and `preventDefault`s every wheel event, correct when
   nothing sits below the hero. Here the bento, Latest and the clusters
   do, so vertical wheel scrolls the page and drag, touch and the arrow
   keys drive the room.
3. **Touch drags horizontally**, not on `clientY`, for the same reason.

Titles also wrap. Gallery captions like "The Starry Night" never need
it; CHM session titles run four times that, so the title wraps to at
most two lines, cuts with an ellipsis past that, and the label plane is
capped so type stays one size across the room.

Nothing from that build ships here. The MoMA artwork, the MoMA mark and
the Signifier font are third-party owned and stay out of this theme.

## Carried back to the app

`--cerebral-amber: 40 100% 64%` and `--ink-amber: 34 100% 28%` are new.
Seth asked for GI to move from blue to a warm amber and the app's
spectrum had no warm hue other than coral, which reads as an alert.
**These need adding to `src/index.css` in the app**, in all three
appearance blocks, or the two properties show GI in different colours.

## Content first, the bento

Ported from the app's `FormatBento` so the two properties match, not
approximated from the Figma render. Twelve columns, not three: row one
is 4+4+4, row two splits 7+5 so the Live card stops short of the format
cards' right edge. Heights are fixed, 21.25rem on row one, 15rem on
row two, so one card's content cannot set every sibling's.

| Card | Media |
|---|---|
| Video | Poster over the chapter list, timecodes in `--anchor` |
| Podcast | 26 bars stepped on a 220ms tick, `--ink-pink` at 45% |
| Editorial | The article text drifting on a 22s loop, two identical passes so the wrap has no seam, under a fade at both edges |
| Live | The `SEP 4` date chip and the next two sessions |
| For clinicians | `Pipeline`, one grey inflow splitting at 40% into three coloured lanes, 120 dots |

The wave runs continuously rather than on hover. Gating it on hover was
wrong twice over: a touch screen never hovers, so on a phone the card
was dead, and the point of the row is seeing all three formats at once
without touching anything.

## Disease cards

The client's arrangement: seven cards in a grid beside the heading,
every card the same height whatever its subtitle runs to.

Cards are near-square (ratio ~1.0 against the reference's 1.09), with
the drawing on a **white** plate at the top and the label sitting on
the floor of the card. `min-height` is what holds that shape: a card
sized only by its own content collapses to the label, and the seven
then step up and down the row by however long each subtitle runs.

The big ghosted chevron behind the intro column fades out down its own
height, the same treatment as the closing band's marks and for the
same reason: a uniform silhouette reads as a shape stamped on the
page, a fading one reads as a watermark under it.

**The drawing carries the hue; the well behind it stays neutral.** The
generated anatomy files ship their own dark ink, so the card uses each
SVG as a CSS mask over a solid fill rather than drawing it directly
that is the only way to recolour artwork that brings its own. The
mask paths live in `home.css` (`.area__art--breast` and siblings), not
in an inline style: a `url()` inside a custom property resolves against
the stylesheet that *consumes* it, so an inline declaration resolved
one directory too deep and 404'd.

**There is no particle field on these cards.** It was tried centred,
then in the corner, then masked and faded, and at every strength it
competed with the drawing, and the drawing is what tells you which
area you are looking at. `DiseaseClusterCard` remains the right
pattern in the app, where the cluster *is* the picture.

## This moment in medicine

A 2x2 grid on the same light ground as the disease cards, not white
cards with a shadow, the two sections sit next to each other, and a
shadowed white card beside a flat grey one reads as two design
systems. Thumbnail on the left, duration pill, title clamped to two
lines; session titles run long enough that an unclamped third line
would set the height of the whole row.

The index numeral is deliberately cropped by the card edge. A number
that fits inside the card reads as content; one running off it reads
as a rule drawn under the content, which is what it is.

The action sits centred under the row rather than beside the heading
(`.band--footaction`): here the link concludes the section instead of
being a shortcut past it.

## The closing band

The CTA and the footer sit on one continuous slate (`--slate`,
`#53606E`), divided by hairline rules rather than by a colour change,
with a ghosted mark flanking each side of the CTA. The marks fade out
down their own height rather than sitting as a flat tint, that
gradient is most of why the band reads as lit from the top instead of
as a slab with shapes stamped on it.

The two marks are one mark rotated, not the same mark printed twice:
the left one is turned 180 degrees. Its fade gradient is authored
upside down (`to top`) to compensate, because the rotation carries the
mask with it and an unflipped gradient would fade the wrong way.

The gap between the CTA and the footer rule used to be about 92px:
`.site-foot` carried 2.5rem of padding *above* its own `::before`
divider, stacked on the CTA's bottom padding. The padding is now 0 and
the CTA's bottom padding is separate from its top.

The footer columns are separated by space, not hairlines. At six
columns the rules read as a table, and the gap already groups them.

The wordmark is **supplied artwork, not live type** (`chm_wordmark()`
in `inc/mark.php`). Its tracking is tighter and its weight heavier
than any Chillax cut sets it, so text only ever approximated it and
made the logo wait on a webfont. The lettering is sized in `em` off
`.site-bar__mark`'s `font-size`, so the chevron-plus-lettering lockup
still scales from one number, and it fills with `currentColor` so the
same file sits ink-on-white in the bar and white-on-slate in the
footer. The link carries `aria-label="CHM, home"` now that the text
node is gone.

The two ghosted marks are large, sit at different heights, and are
each cropped by a different edge of the band. Flush-topped and level
they read as a repeated tile; offset and cut they read as one mark
passing behind. The secondary button is outlined rather than filled:
two solid buttons side by side compete, and the teal is the one meant
to be pressed.

The footer columns are **one horizontal scroller at every width**.
They do not reflow into a grid: reflowing produced a different shape
at each breakpoint, and one long row reads the same on a phone and on
a desktop. Verified at 375px, 521px and 1440px, one row throughout,
1070px of scroller inside 375px, and no page-level overflow. The row
is focusable so it can be scrolled from the keyboard.

`--slate` was read off the Figma prototype by eye, because a prototype
canvas cannot be pixel-sampled. It is close but not sourced; if the
exact value exists in the brand file, use that instead.

## Placeholders that need real content

| Placeholder | Where | Needs |
|---|---|---|
| Clinician monograms (AB, IK, MP, MM, MD) | In conversation | Real portraits. A video still is not a portrait, and using one would put the wrong face against a named clinician |
| "Real people. Brighter tomorrow." on all four shows | Podcast network | One tagline per show. The Figma carries the same line four times, which reads as unfinished copy |
| "Episodes" | Podcast network | Episode counts, once `/api/podcasts/:id/episodes` is wired |
| "Next: 4 Sep" | Content first, Office Hours | The next real session date |

## Appearance

**Light only.** The brand guide specifies a light system with a dark
anchor (Deep Expertise as a surface), not a dark theme. A
`prefers-color-scheme: dark` block used to sit in `tokens.css` and it
still carried the *pre-reskin* palette, so a visitor on a dark-mode OS
was served near-black with the old blue primary and magenta accent
an entirely different design from the one signed off. It is removed.
Reinstating dark means deriving it from the guide, not reviving those
values.

## Tokens the other public pages need

`tokens.css` was rebuilt from the brand guide and declared 60 tokens;
the app's `index.css` declares 115. The 55-token gap was every name
the *other* public pages reach for: a `--color-*` layer of
ready-to-use colours, a motion set, per-disease aliases, shadow and
radius names. A page ported without them resolves those `var()` calls
to nothing, invisible text, no borders, no transitions.

They are now bridged at the bottom of `tokens.css`, **mapped to the
new brand values**. Copying the app's definitions across would have
quietly reinstated the old palette the way the dark block did, one
component at a time.

`--tw-ring-color` and `--tw-shadow-color` are deliberately absent:
they are Tailwind internals, and a component that needs them is
carrying a Tailwind utility that has to be rewritten as plain CSS
during the port. A fallback here would hide that.

## Routes the nav points at that do not exist

Checked against `App.tsx` on `feature/new-ui`:

| Link | State |
|---|---|
| `/podcasts` | **Behind login.** The routes are `/app/podcasts`, inside `ProtectedRoute` |
| `/editorial` | **No route at all**, public or protected |

The header nav, the bento, the footer and the whole Podcast network
section link to both. Either podcasts becomes public when the public
site moves to WordPress, or those links need a destination. This is a
decision, not a bug to fix in CSS.

## House style

No em-dashes, anywhere: not in copy, not in comments. WordPress's own
`document_title_separator` is an en dash, so `chm_title_separator()`
replaces it with a middle dot. Verified across every public route.

## Not in this theme

The template hierarchy now covers the library, term archives, single
posts, static pages, search and 404, everything WordPress routes on
its own. What is **not** built are the pages that are their own
compositions rather than a heading over a grid:

| Route | App component | Why it needs its own template |
|---|---|---|
| `/podcasts`, `/podcasts/:show` | `Podcasts`, `PodcastShow` | Show grounds, episode lists, a player |
| `/live`, `/live/:id` | `PublicWebinars`, `PublicWebinarDetail` | Session states, registration |
| `/chm-office-hours` | `PublicOfficeHours` | Same, plus question submission |
| `/kol-network` | `DolNetwork`, `KolProfilePage` | Region map, profiles |

The auth routes stay in the React app and should not be ported:
`/login`, `/join`, `/verify-email`, `/forgot-password`,
`/reset-password/confirm`, `/mfa/setup`, `/auth/callback`,
`/complete-profile`, `/admin/login`. They need sessions, OAuth
callbacks and MFA, none of which belong in a theme.
