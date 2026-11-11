---
name: playground-performance
description: >-
  Find The SEO Framework hotspots with triggered Xdebug profiles in WordPress
  Playground. Inspect Cachegrind, self/inclusive costs and caller/callee edges,
  or evaluate same-plugin changes. Diagnostics do not predict production performance.
---

# Playground hotspot profiling

Shared implementation: [wp-plugin-performance](https://github.com/theseoframework/wp-plugin-performance),
using the existing [wp-plugin-regression](https://github.com/theseoframework/wp-plugin-regression)
launcher. Keep runtime code in those engines; this repository supplies only
plans, discovery and a thin dispatcher. Read the installed engine README.

## Scope and interpretation

Playground diagnostics do not predict production performance. PHP-WASM, SQLite
translation, filesystem behavior and Xdebug overhead can change absolute timings
and relative operation costs. Use profiles to identify candidate hotspots, then
confirm useful gains without profiling on representative native WordPress hosting.
Preserve output, filters and observable behavior when optimizing.

Inclusive times overlap; do not add parent and child costs. Frequent calls can
look disproportionately expensive under Xdebug. Call counts include profiler
entries such as generator resumptions. Do not publish competitor comparisons,
product rankings or projected native savings from these results.

## Permission and dependencies

Before acquisition or a live command, explicitly read
`.local/playground/permission.txt`: exactly `PLAYGROUND=True` or
`PLAYGROUND=False`.

- Missing: obtain the user's decision before acquiring dependencies or launching.
- False: skip acquisition and live work; do not ask repeatedly.
- True: honor the authorized request and budget without reconfirming.

Never invent a flag or copy consent from another consumer. Setup-only work does
not authorize live requests. Offline `hotspots`, `prune`, `report`, `export`, `doctor`,
help and synthetic tests do not launch WordPress and need no live consent.

Use already installed engines and matching PHP/Xdebug artifacts. Missing software
must produce an actionable explanation, not an automatic installation or silent
fallback to timing. Live profiling needs a JSPI-capable Node runtime and the
regression launcher's `wpPluginPerformanceApi: 1` support for per-site OPcache
and CLI overrides. Keep OPcache enabled; do not add cache-off or JIT experiments
without an explicit request. No workflow here requires Git commands.

Keep engine locations in private
`.local/playground/performance/engine.json`, using `directory` and
`regressionDirectory`; explicit `--regression-dir` is also supported.
The consumer root is this plugin repository. Keep workstation paths, credentials,
profiles, backups and historical comparisons out of public skill files.
Read [editor integration](references/editors.md) when maintaining discovery.

## Capture

`profile` defaults to [profile-plan.json](profile-plan.json). Its WordPress/PHP
versions are explicit example inputs, not promises about installed or latest
releases. Adapt a private copy to the actual installed versions before live use.
It selects one local TSF plugin and five HTML routes: blog homepage, static
homepage, posts page, post and page. Three unprofiled warmups and one triggered
capture per route mean twenty page requests and five raw files, plus setup/control
probes. Use `--profiles` for an authorized repeat on selected routes.

```text
node .claude/skills/playground-performance/scripts/performance.js doctor
node .claude/skills/playground-performance/scripts/performance.js profile --config .local/profiling/plan.json
```

The engine mounts independent source copies, checks original/executed PHP hashes,
and uses fresh PHP tokens, real admin cookies and native route/auth/status/body
checks. Triggered requests must produce unique complete Cachegrind files.
PHP/SQL diagnostics, missing Xdebug or source/activation drift reject the run.
Stop only the exact owned session; retain invalid evidence.

The disposable profiling probe blocks WordPress HTTP and mail. Browser requests
and direct non-WordPress networking are outside that guard. Never install the
probe on production or alter plugin code to satisfy broken instrumentation.

## Inspect and evaluate

Deliver `report.html`, `hotspots.json`, `provenance.json` and function/edge CSV.
Include raw Cachegrind paths if retained. Use plugin-only self time to find local work, then
inspect inclusive time, call counts and direct caller/callee edges. KCachegrind
or QCachegrind can open the raw files. Keep ordinary HTML, content-heavy pages,
admin, RSS and sitemap XML/XSL/CSS in their own cohorts.

```text
node .claude/skills/playground-performance/scripts/performance.js hotspots --input .local/profiling/saved-run --output .local/profiling/review
```

Saved-run reports preserve recorded runtime/source/request identity and the
original manifest hash. Standalone Cachegrind imports lack validated request and
source provenance; missing historical fields stay unknown. Use new output
directories and preserve old evidence. Report-only requests authorize no new calls.

Use the shared engine's raw retention policy. Check `rawRetention` in the
private engine.json and ask the user if it is unset. Record `keep` or `prune`
from the user's decision, or pass `--raw-retention=keep|prune` for a single
run. With no decision, retain the files. For authorized cleanup of an existing
run, use `prune --input <run>` to preview and add `--apply` to execute.
Keep raw profiles needed for KCachegrind, re-parsing or investigation.

Use the shared engine's `tools/parsed-cache.py` for loose legacy parsed
Cachegrind JSON. It consolidates an exact cache directory into a verified ZIP.
Restore the loose files before rerunning historical analysis scripts.

Optional unprofiled same-plugin timing uses [plan.json](plan.json) through
`run` or `prepare`; these commands keep their separate timing default.
Its runtime pins are examples too; adapt a private copy to installed versions.
Only one plugin identity and inactive WordPress controls are supported. Read the
installed engine's `docs/comparisons.md`, `docs/methodology.md` and
`docs/runtimes.md` for that mode. A helper percentage or isolated minimum
does not establish a page gain.

On Windows keep child consoles hidden. After integration changes, run
`node .claude/skills/playground-performance/scripts/test.js <performance-engine>`.
This uses a synthetic engine and validates plans offline; it never launches
WordPress. Use `performance.js test` for the shared engine's synthetic suite.
