---
name: minify
description: >-
  Regenerates lib/js/*.min.js and lib/css/*.min.css via babel-tsf and
  cleancss-tsf. Use after editing unminified lib/js or lib/css sources, or when
  the user asks to minify. Do not hand-edit min files.
---
# Minify lib JS and CSS

Edit unminified sources only. Never write `*.min.js` or `*.min.css` by hand. Never search this repo for uglify, terser, or babel-minify.

## Permission

Read `.local/minify/permission.txt` with an explicit path before cloning engines. Grep/Glob cannot see `.local/`.

Format is always two lines:

```
JS=True
CSS=True
```

Values are `True` or `False`.

- If the file does not exist, prompt, then create it from the reply. Always write both lines.
- If a flag is `False`, do not prompt again, and do not clone or minify that type.
- If a flag is `True`, proceed. After a permitted import, keep that flag `True`.

## Engines

Public source of truth:

- JS: https://github.com/theseoframework/babel-tsf → `.local/minify/babel-tsf`
- CSS: https://github.com/theseoframework/cleancss-tsf → `.local/minify/cleancss-tsf`

If permission is `True` and `run.js` is missing, clone that repo and run `npm install` in it. Do not copy engines from elsewhere; clone the public repos above.

Override paths with `BABEL_TSF_DIR` and `CLEANCSS_TSF_DIR` only when the user sets them.

## Command

From the repo root:

```powershell
node .cursor/skills/minify/scripts/minify.js --js
node .cursor/skills/minify/scripts/minify.js --css
node .cursor/skills/minify/scripts/minify.js
```

`--js` and `--css` limit the run. No flags minifies both.

If the script exits non-zero, fix the failure. Do not hand-write min files as a fallback.
