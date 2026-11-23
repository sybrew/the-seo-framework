---
name: pre-release
description: >-
  Runs the pre-release checklist. Sets the readme.txt Stable tag to the
  version being released, captures every page type (sitemaps, robots, feeds,
  and the rest of the catalog), and flicks all buttons that the changelog
  mentions. Use when preparing a release, shipping a WordPress.org update, or
  when the user asks for the pre-release checklist.
---

# Pre-release

Run when cutting a release, or when the user asks for this checklist. Do not strip `-dev` or move `Stable tag` during ordinary development.

Follow the [Playground](../playground/SKILL.md), [copyright](../copyright/SKILL.md), [minify](../minify/SKILL.md), and [Makepot](../makepot/SKILL.md) skills for their respective checks. Do not tag, commit, or push.

For an explicit release benchmark or timing revalidation request, select the matching [workspace instructions](../../../.github/instructions/local.instructions.md#load-local-instruction-files). Use verified archived evidence for drafting.

## Version headers

The release version is `THE_SEO_FRAMEWORK_VERSION` in `autodescription.php`.

On the release commit, set these to that version:

- Plugin header `Version:`, with no `-dev-{number}` suffix
- `readme.txt` `Stable tag:`
- The first changelog heading, which starts `### {version}`
- `== Upgrade Notice ==` entry `= {version} =` when the changelog tells users to do something after updating

`Stable tag:` is the version WordPress.org offers for download. It is a version number, never `trunk`. Leaving it on the previous release means the new version is not offered. 5.2.0 shipped with `Stable tag: 5.1.4`.

Until that commit, `Version:` keeps its `-dev-{number}` suffix and `Stable tag:` stays on the previous release.

`Requires at least`, `Tested up to`, and `Requires PHP` in the plugin header match the same lines in `readme.txt`. Bump `Tested up to` only when this checklist's Playground run used that WordPress version.

If `bootstrap/upgrade.php` gained a `_do_upgrade_{id}` function, `{id}` is in the `$versions` list and `THE_SEO_FRAMEWORK_DB_VERSION` is `{id}`. Otherwise leave the database version.

## WPML Compatibility

Email WPML before release if any changes affect how TSF works with WPML.

## Every page type

`launch --pair`. wordpress.org is before, the working tree is after. Do not compare against `baseline.json`.

Capture both labels with no `--feature` and no `--types`. That walks every `plugin.json` entry: both front frames, posts, pages, archives, search, 404, login, the custom route, sitemap, robots.txt, feeds, redirects, and oEmbed.

`compare` the two bundles with no `--feature`. A PHP diagnostic is a blocker. A diff the changelog does not describe is a blocker. If wordpress.org is still the previous release because `Stable tag` was never updated, say so.

## WooCommerce

Every release requires a fresh Playground pair with the same current stable WooCommerce ZIP mounted via `--extra-plugin-zip`. Use `--site-before=woocommerce-before --site-after=woocommerce-after`, then run:

```
node .claude/skills/pre-release/scripts/woocommerce.js --site=woocommerce-before --output=.local/playground/captures/woocommerce-before.json
node .claude/skills/pre-release/scripts/woocommerce.js --site=woocommerce-after --output=.local/playground/captures/woocommerce-after.json
```

Both runs must complete; the after run must pass. A baseline failure is acceptable only when the changelog describes it and the after run demonstrates its fix. Compare the reports and startup logs; an unexplained difference or new PHP diagnostic blocks release.

The runner checks commerce URLs, canonicals, breadcrumbs, uncached sitemaps, permalink changes, category URL collisions, and independent base removal for categories and product categories, including product-category feeds and pagination. Older TSF releases without taxonomy base removal run the commerce baseline. It restores its changed settings and removes its fixture objects afterward, and verifies that cleanup succeeded.

## Changelog buttons

Read this version's changelog section. List every checkbox, select, radio, or field it names. Skip bullets that name no control.

On the after site, use the [Playground skill's Playwright path](../playground/SKILL.md). Do not type a password.

Open `wp-admin/admin.php?page=theseoframework-settings`, or the post, term, or user screen the changelog names. Flick each control to the value the changelog describes, save, and confirm the effect. Use `capture --paths` when the effect is front-end output. Flick it back when a later check needs the default.

A control you cannot find is a failed item. If Playwright is not connected, say so. Do not claim the controls were flicked.

## Translation template

Run the [Makepot skill](../makepot/SKILL.md) last, after the release header and Stable tag are final and the other checks are complete. Compare the generated message strings with the previous template and verify the template header uses the release version.

## Report

List each item as pass or fail: the release version, `Stable tag`, the compare summary, both WooCommerce reports with the tested versions, and each control you flicked with the effect you saw.
