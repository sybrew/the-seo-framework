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

Playground 301s `/sitemap.xml` via a VFS mu-plugin (`sitemap-redirect.php`). The engine overwrites that file with a no-op until https://github.com/WordPress/wordpress-playground/issues/4325 is patched. Drop the overwrite when that issue lands. If every request 500s with a parse error in that file, the no-op was invalid PHP. Fix `lib/launch.js` in wp-plugin-regression; do not change the plugin.

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
node .cursor/skills/playground/scripts/playground.js capture --label after --feature=title
node .cursor/skills/playground/scripts/playground.js compare --before before --after after --feature=title
node .cursor/skills/playground/scripts/playground.js surfaces
node .cursor/skills/playground/scripts/playground.js harness --action ping
node .cursor/skills/playground/scripts/playground.js harness --json-file .local/playground/payload.json
```

Optional launch flags: `--wp=latest`, `--php=8.3`, `--site=default`, `--plugin=working|wporg`, `--port=9400`.

## WordPress and PHP versions

`--wp` is a Playground **build slug**, not a path to Core. `stop` first, then launch with a **new `--site`**. Do not reuse a site SQLite across majors or `latest` vs `trunk`.

Slugs the CLI accepts: `latest` (default gold), `beta`, `trunk` (`nightly` is the same), a hosted major/minor (`7.0`, `6.9`, `6.9.1`), a beta/RC (`6.8-RC1`), or a zip URL. PHP is `--php` (`7.4`–`8.5`, default `8.3`).

`--wp=trunk` is the prebuilt WordPress/WordPress nightly. It is not `wordpress-develop` and not `--wp=7.2`. `--wp=7.2` only works if Playground hosts a 7.2 release or beta zip. Do not mount `wordpress-develop/src` (or its `build/`) as `/wordpress`. That is not implemented.

Use `--wp` / `--php` only when the change can be version-sensitive. Not a full matrix on every edit. Recapture on that site. Do not compare a 7.0 or trunk dump to the `latest` `baseline.json`.

## A/B previous vs current

The working tree is mounted live. PHP edits apply on the next request. There is no “previous plugin” left on disk after you edit. Pick one previous:

1. **This session, not yet edited.** `capture --label before --feature=<name>`, edit, `capture --label after --feature=<name>`, `compare --before before --after after --feature=<name>`.
2. **Already edited, or vs gold.** `capture --label after --feature=<name>`, `compare --before baseline --after after --feature=<name>`. Requires `.local/playground/captures/baseline.json` from the same catalog/seed on `latest`. If that file is missing or the catalog changed, say so. Do not capture `before` from the dirty tree and call it previous.
3. **wordpress.org release vs this tree.** `launch --plugin=wporg`, `capture --label before --feature=<name>`, `stop`, `launch --plugin=working` with the **same** `--site`, `capture --label after --feature=<name>`, `compare`.

Same `--feature` (or `--types`) on both captures. `surfaces` first. Do not git checkout, stash, or mount another Core tree to fake previous. Unminified `lib/js` / `lib/css` edits need the minify skill before capture.

Front-end SEO A/B is HTTP `capture` / `compare`. Admin SEO UI, settings, and REST-from-the-browser A/B is Playwright MCP against the live Playground URL after `launch`. The engine does not drive a browser. `playwright.env` in Cursor settings is the Test extension, not the agent. Cursor’s built-in Browser Automation conflicts with Playwright MCP; use the MCP. Default captures stay logged-out.

Playwright MCP must load `.cursor/skills/playground/playwright.mcp.json` (`--config` in `%USERPROFILE%\.cursor\mcp.json`). That file strips Chrome's `--disable-blink-features=AutomationControlled` flag and sends `X-TSF-Playground-Admin: 1`. For admin/REST, navigate to `http://127.0.0.1:9400/wp-admin/` (or `/wp-json/`). Do not fill `admin` / `password` — Cursor Auto-review blocks that. `tsf-playwright-admin.php` calls `wp_set_auth_cookie()` when the header is present. That is the login. Do not add `.cursor/permissions.json`. Do not pass Playground `--login` or a blueprint `login` step (HTTP capture would share that session). Front-end HTML stays logged-out. Restart the Playwright MCP server after changing `mcp.json` or the config.

Mutate the live site by writing mu-plugins under `.local/playground/sites/{id}/mu-plugins/`, or `harness --json-file <path>`. Do not pass `--json "{...}"` from PowerShell; it strips the quotes. Do not add `eval`.

The catalog is `plugin.json` `entries`, `surfaces`, and `surfaceLines`. One capture writes `.local/playground/captures/<label>.json` — the full walkable dump (status, headers, marker block, plus `headTags`). It is a local gold file, not a CI gate. Do not hand-edit it. Regenerate when the seed or catalog changes. `compare --feature` needs those entries in both bundles; recapture with the same `--feature`.

On output-affecting work:

1. `surfaces` — feature → page types, then each entry id and path.
2. Pick the feature you touched (`title`, `description`, `robots`, `canonical`, `schema`, `og`, `twitter`, `sitemap`, `robots-txt`, `feed`, `redirect`, `oembed`).
3. `capture --label after --feature=<name>` and `compare --before baseline --after after --feature=<name>`. The command prints that list, then diffs only those pages and only the lines for that feature (`surfaceLines`), plus status/location.

`--types=post,page` narrows to page types. `--paths` still appends extra URLs. A full compare (no `--feature`) diffs the whole extracted block.

Seeded types: front as blog, static front, posts page, post, page, `?p=` query, category, empty category, tag, date (day/month/year), author, search, 404, paged blog / posts page / category, multipage post, hierarchical CPT + child, non-hierarchical CPT, both CPT archives, a taxonomy on two CPTs, a taxonomy on one CPT, sitemap, robots.txt, site/post/category/author/CPT/comments feeds, post/term redirects, oEmbed. Attachment, preview, comment-paged, and WooCommerce shop/product are not seeded.

The seed waits for `THE_SEO_FRAMEWORK_PRESENT` and `is_blog_installed()`. Install requests must not mark the catalog ready. Redirect meta must be an http(s) URL (`home_url( '/sample-page/' )`); TSF strips relative paths when resolving the redirect.

`headMarkers` is the unambiguous TSF wrap. `headTags` are regexes run on `<head>` (attribution unknown: theme, core, or TSF). TSF’s `<title>` is only in `headTags` because it is filled via `pre_get_document_title`, not printed inside the wrap. Do not hardcode tag names in the engine.

To test other posts or terms, create or update them with harness, then recapture with `--paths`. Use the `path` from the harness reply.

```
{"action":"post","title":"Harness post","slug":"harness-post","meta":{"_genesis_title":"Custom post title"}}
{"action":"term","name":"Harness cat","slug":"harness-cat","meta":{"doctitle":"Custom term title"}}
{"action":"meta","type":"post","id":1,"key":"_genesis_title","value":"Hello meta"}
{"action":"meta","type":"term","id":1,"key":"doctitle","value":"Uncategorized meta"}
```

Post meta keys are TSF’s per-post fields (`_genesis_title`, `_genesis_description`, `_genesis_noindex`, …). Term meta is the `autodescription-term-settings` bag (`doctitle`, `description`, `noindex`, …), or that bag name with an object value.

`stop` waits for the site SQLite file to unlock. If launch still dies with a SQLite error, use `--site` with a new id or delete `sites/<id>/database/.ht.sqlite`.

If the script exits non-zero, fix the failure.
