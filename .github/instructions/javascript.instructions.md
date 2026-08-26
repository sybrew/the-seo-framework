---
description: "Use when editing JavaScript files in this repository. Covers TSF JavaScript syntax and formatting expectations."
applyTo: "**/*.js"
---

# JavaScript Rules

- Use ES6+.
- Do not write constant functions.
- Do not use JSX.
- Apply PHP spacing standards, including vertical alignment.
- After a `function` opening `{`, insert a blank line when the body has two or more statements. A single-statement body stays tight. A leading comment is not a statement: comment plus a single `return` or `yield` stays tight. With two or more statements, the blank line comes first so the first comment sits on the next interior line. Do not insert that blank after `if`, `else`, loop, or `try` braces.
- A `return` or `yield` that ends that interior always has a blank line before it if that interior has two or more statements. Early `return` / `yield` inside `if` / `else` count: the blank is a visual split that this path ends there. A single-statement interior that is only a `return` or `yield` stays tight. Own-line comments immediately above that `return` or `yield` stay attached to it; the blank line goes before that comment group. Do not insert that blank before `continue` or `break`. After an early `return`, `continue`, or `break` guard, insert a blank line before the next sibling statement, including another guard.
- For `=> {`, add a newline after `{` when the body has two or more statements and the first is not `return`, or when the only statement is a multi-line braced `if`, `for`, `while`, or `switch`. Stay tight for a wrapped `return` or call, an unbraced one-line `if`, and a `try`/`catch` around a single call. Concise `=> expr` without braces stays as-is.
- Ignore long `__`-prefixed properties for spacing and alignment purposes.
- Use `const` instead of `import`.
- Do not add parentheses to lone parameters in arrow functions.
- Put each chained method call on a new line.
- Do not create, edit, or search for `*.min.js`.
