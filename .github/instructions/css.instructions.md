---
description: "Use when editing CSS files in this repository. Covers TSF stylesheet conventions and layout-debugging expectations."
applyTo: "**/*.css"
---

# CSS Rules

- When debugging CSS spacing or layout issues, always read the full HTML template structure first to understand nesting, flex and grid contexts, and how gap, margin, and padding compound across parent-child relationships.
- Use lowercase hex colors.
- Remove the zero before decimal points.
- Close the last property with a semicolon.
- Do not create, edit, read, grep, search, or `git diff` `*.min.js` or `*.min.css`, including after minify exit code 0.
