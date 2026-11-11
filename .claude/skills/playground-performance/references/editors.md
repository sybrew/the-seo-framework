# Editor integration

The canonical skill, dispatcher, and plans live under
`.claude/skills/playground-performance/`. Claude, Cursor, and VS Code Local
discover this skill directly. VS Code forwards it to its Copilot harness
through its default skill locations. Codex reads the direct skill route in
`AGENTS.override.md`.

Edit shared guidance in `AGENTS.md` and scoped rules in
`.github/instructions/`. After changing either source or skill metadata,
regenerate the native rule files and the Codex startup file:

```text
node .github/scripts/sync-agent-instructions.js --write
node .github/scripts/sync-agent-instructions.js --check
```

`performance.js` dispatches to the installed wp-plugin-performance engine.
Use its `run`, `export`, and `test` commands for timing, exporting saved data,
and running the shared synthetic suite.

`profile` defaults to `profile-plan.json`. Optional same-plugin `run` and
`prepare` default to `plan.json`. An explicit `--config` overrides the default.
A resumed session uses its recorded plan. Profiling takes explicit WordPress
and PHP versions and one plugin. Timing uses candidates for the same plugin
and optional inactive controls.

Check the consumer without starting WordPress:

```text
node .claude/skills/playground-performance/scripts/performance.js --help
node .claude/skills/playground-performance/scripts/test.js <performance-engine>
```

Offline inspection, export, pruning previews, and synthetic tests need no live
consent. Live commands require the consumer's recorded decision. Playground
diagnostics do not predict production performance.

Use existing clients to check native discovery and leave global settings alone.
