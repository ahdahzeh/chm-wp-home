# tools/

Not part of the theme. WordPress never loads anything in here.

## `render-preview.php`

A throwaway WordPress stand-in, just enough of core to run
`front-page.php`, `header.php` and `footer.php` outside an install, so
the PHP can be checked before it reaches a server.

```bash
CHM_THEME="$PWD" php -d error_reporting=E_ALL -d display_errors=1 \
  tools/render-preview.php > preview/wp-render.html
```

Then open `preview/wp-render.html` next to `preview/index.html` and
compare. The asset paths are written as `../assets/…` so both files
resolve against the same directory.

The fixtures are twelve invented posts with real-looking titles,
formats and durations. They exist to exercise the templates, not to
predict what the install holds, a section that looks right here can
still look wrong against 525 real posts.
