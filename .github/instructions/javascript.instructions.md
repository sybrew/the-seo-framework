---
description: "TSF JavaScript syntax, formatting exceptions, and minification after source edits."
applyTo: "**/*.js"
---

# JavaScript Rules

- Use ES6+.
- Do not write constant functions.
- Do not use JSX.
- For `=> {`, add a newline after `{` when the body has two or more statements and the first is not `return`, or when the only statement is a multi-line braced `if`, `for`, `while`, or `switch`. Stay tight for a wrapped `return` or call, an unbraced one-line `if`, and a `try`/`catch` around a single call. Concise `=> expr` without braces stays as-is.
- Ignore long `__`-prefixed properties for spacing and alignment purposes.
- Use `const` instead of `import`.
- Do not add parentheses to lone parameters in arrow functions.
- Put each chained method call on a new line.
- Add trailing commas to multiline parameter lists.
- After editing unminified `lib/js` sources, follow the [minify skill](../../.claude/skills/minify/SKILL.md) using the recorded JS permission.
