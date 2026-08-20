---
description: "Use when editing JavaScript files in this repository. Covers TSF JavaScript syntax and formatting expectations."
applyTo: "**/*.js"
---

# JavaScript Rules

- Use ES6+.
- Do not write constant functions.
- Do not use JSX.
- Apply PHP spacing standards, including vertical alignment.
- Add a newline after a `function` opening brace unless the body is a single line or an instant `return`.
- For `=> {`, add a newline after `{` when the body has two or more statements and the first is not `return`, or when the only statement is a multi-line braced `if`, `for`, `while`, or `switch`. Stay tight for a wrapped `return` or call, an unbraced one-line `if`, and a `try`/`catch` around a single call. Concise `=> expr` without braces stays as-is.
- Ignore long `__`-prefixed properties for spacing and alignment purposes.
- Use `const` instead of `import`.
- Do not add parentheses to lone parameters in arrow functions.
- Put each chained method call on a new line.
- Do not create, edit, or search for `*.min.js`.
