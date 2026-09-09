---
description: "Use when editing JavaScript files in this repository. Covers TSF JavaScript syntax and formatting expectations."
applyTo: "**/*.js"
---

# JavaScript Rules

- Use ES6+.
- Do not write constant functions.
- Do not use JSX.
- Apply PHP spacing standards, including vertical alignment.
- Align object `:` with spaces after the separator.
- After a `function` opening `{`, insert a blank line when the body has two or more statements. A single-statement body stays tight. A leading comment is not a statement: comment plus a single `return` or `yield` stays tight. With two or more statements, the blank line comes first so the first comment sits on the next interior line. Do not insert that blank after `if`, `else`, loop, `try`, or labeled `{` (`label: {`) braces.
- A `return` or `yield` that ends that interior always has a blank line before it if that interior has two or more statements. Early `return` / `yield` inside `if` / `else` count: the blank is a visual split that this path ends there. A single-statement interior that is only a `return` or `yield` stays tight. Own-line comments immediately above that `return` or `yield` stay attached to it; the blank line goes before that comment group. Do not insert that blank before `continue` or `break`. After an early `return`, `continue`, or `break` guard, insert a blank line before the next sibling statement, including another guard.
- Compact `if` / `else if`: keep a void `return;`, `continue;`, or `break;` on the same line when that line is at most 80 columns (tab = 4). Put a `return` with a value on the next line, unbraced. If that compact void line exceeds 80, put the terminator on the next line, unbraced. Do not wrap a single expression (no `&&` / `||`) just to make room for the terminator. If an `&&` / `||` condition line exceeds 80, wrap at those operators (first operand padded by 3 spaces) and use braces. Do not wrap only the `return`.
- For `=> {`, add a newline after `{` when the body has two or more statements and the first is not `return`, or when the only statement is a multi-line braced `if`, `for`, `while`, or `switch`. Stay tight for a wrapped `return` or call, an unbraced one-line `if`, and a `try`/`catch` around a single call. Concise `=> expr` without braces stays as-is.
- Ignore long `__`-prefixed properties for spacing and alignment purposes.
- Use `const` instead of `import`.
- Do not add parentheses to lone parameters in arrow functions.
- Put each chained method call on a new line.
- Add trailing commas on multiline calls, arrays, objects, and parameter lists.
- Do not create, edit, or search for `*.min.js`.
