---
name: playground
description: >-
  Launches a local WordPress Playground site with The SEO Framework mounted,
  captures front-end SEO artifacts, and compares before/after plugin states.
  Use when testing regressions, reproducing bugs, changing meta/sitemap/robots/
  schema/oEmbed/admin SEO output, checking WordPress version behavior, plugin
  conflicts, or before committing output-affecting work.
---
# Playground regression

Do not write PHPUnit. Do not use Local WP, Docker, or MySQL. The engine is wp-plugin-regression.

If launch, capture, harness, permalinks, sitemap, robots, or compare looks wrong, decide whether the cause is the plugin or the regression plumbing (Playground, engine, mounts, cookies, rewrites, mu-plugins, the harness). When the plumbing is at fault, say so in the chat. Fix or report that system. Do not work around it in plugin code. Never change The SEO Framework so it fits a broken harness.

When **not** to boot: wrap, trailing-comma, minify-only, docs-only.

When to run:

- Output-affecting work (meta, robots, sitemap, schema, oEmbed, admin SEO UI)
- Support reproduction
- Before calling the work done

Keep one server per session. PHP edits apply on the next request. Restart only for `--wp` / `--php`, a different `--site`, or `--plugin=wporg` vs `working`.

Captures are logged-out. Launch does not pass `--login`.

Do not `flush_rewrite_rules()` in a blueprint `runPHP` step. That writes incomplete rules and post permalinks fall through to the homepage. Set `permalink_structure` and `delete_option( 'rewrite_rules' )` so Playground can flush on a later `init`.

If `.local/playground/run.json` points at a live pid, reuse it.

## Permission

Read `.local/playground/permission.txt` with an explicit path before cloning the engine. Grep/Glob cannot see `.local/`.

Never invent `True` or `False`. Never write this file unless the user has replied with the flag.

Format is always one line (`True` or `False`, never guessed):

```
PLAYGROUND=True|False
```

- If the file does not exist, prompt. If you cannot prompt and no permissions exist, skip Playground altogether and ask at the end of the chat.
- After the user replies with the flag, create the file from that reply.
- If the flag is `False`, do not prompt again, and do not clone or launch.
- If the flag is `True`, proceed. After a permitted import, keep that flag `True`.

The playground script reads this file. A missing file is a hard stop. A `False` flag skips even if the engine is already cloned.

## Engine

Public source of truth:

- https://github.com/theseoframework/wp-plugin-regression → `.local/playground/wp-plugin-regression`

If permission is `True` and `run.js` is missing, clone that repo and run `npm install` in it. Do not copy the engine from elsewhere; clone the public repo above.

Override the path with `WP_PLUGIN_REGRESSION_DIR` only when the user sets it (local engine clone, e.g. `C:\GitHub\wp-plugin-regression`).

## Command

From the repo root:

```
node .cursor/skills/playground/scripts/playground.js launch
node .cursor/skills/playground/scripts/playground.js stop
node .cursor/skills/playground/scripts/playground.js capture --label before
node .cursor/skills/playground/scripts/playground.js capture --label after --paths=/harness-post/,/category/harness-cat/
node .cursor/skills/playground/scripts/playground.js compare --before before --after after
node .cursor/skills/playground/scripts/playground.js harness --action ping
node .cursor/skills/playground/scripts/playground.js harness --json-file .local/playground/payload.json
```

Optional launch flags: `--wp=latest`, `--php=8.3`, `--site=default`, `--plugin=working|wporg`, `--port=9400`.

Re-boot with `--wp=6.7` and `--php=7.4` only when the change can be version-sensitive. Not a full matrix on every edit.

Default compare is session recapture (no restart). Release vs working tree: `launch --plugin=wporg`, capture `before`, `stop`, `launch --plugin=working` with the same `--site`, capture `after`, `compare`.

Mutate the live site by writing mu-plugins under `.local/playground/sites/{id}/mu-plugins/`, or `harness --json-file <path>`. Do not pass `--json "{...}"` from PowerShell; it strips the quotes. Do not add `eval`.

Default capture paths are the stock post, page, category, and author. To test other posts or terms, create or update them with harness, then recapture with `--paths` (comma-separated, and/or repeated `--path`). Use the `path` from the harness reply.

```
{"action":"post","title":"Harness post","slug":"harness-post","meta":{"_genesis_title":"Custom post title"}}
{"action":"term","name":"Harness cat","slug":"harness-cat","meta":{"doctitle":"Custom term title"}}
{"action":"meta","type":"post","id":1,"key":"_genesis_title","value":"Hello meta"}
{"action":"meta","type":"term","id":1,"key":"doctitle","value":"Uncategorized meta"}
```

Post meta keys are TSF’s per-post fields (`_genesis_title`, `_genesis_description`, `_genesis_noindex`, …). Term meta is the `autodescription-term-settings` bag (`doctitle`, `description`, `noindex`, …), or that bag name with an object value.

`stop` waits for the site SQLite file to unlock. If launch still dies with a SQLite error, use `--site` with a new id or delete `sites/<id>/database/.ht.sqlite`.

If the script exits non-zero, fix the failure.
