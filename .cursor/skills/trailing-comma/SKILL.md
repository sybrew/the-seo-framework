---
name: trailing-comma
description: >-
  Adds trailing commas on multiline PHP/JS calls, arrays, and objects. Use when
  wrapping arguments, writing multiline apply_filters/do_action/sprintf, or when
  a closing ) or ] sits on its own line without a comma on the last argument.
---
# Trailing commas

Source of truth: `.github/copilot-instructions.md` — trailing commas at the end of multiline object/array properties and function arguments when the language supports it.

## PHP 7.4

Put a trailing comma on multiline:

- Function and method **calls** (`apply_filters(`, `sprintf(`, `Escape::css_content(`)
- `array( … )` and `[ … ]` literals

Do **not** put a trailing comma on:

- Function, method, closure, or `fn()` **parameter lists** (PHP 8.0)
- Closure `use ( … )` lists (PHP 8.0)
- `if (` / `elseif (` / `for (` / `foreach (` / `while (` / `switch (` / `catch (` conditions
- Single-line calls or arrays

## JavaScript

Same rule for multiline calls, arrays, and objects. Parameter lists may have trailing commas (ES2017). Do not edit `*.min.js`.

## Shapes

Comma after the last item of a multiline call, array, or object. A docblock after `(` does not make it single-line.

```php
foo( $a, $b );           // no — one line
foo(
	$a,
	$b,                  // yes
);
bar(
	baz( $x ),           // yes — bar is multiline
);
bar(
	baz(
		$x,              // yes — both
	),
);
[
	'a' => 1,
	'b' => 2,            // yes
];
function f( $a, $b ) {}  // no — parameters (PHP 8.0)
use ( $c )               // no — closure use (PHP 8.0)
if ( $a && $b ) {}       // no — condition
```

```js
fn( a, b );              // no — one line
fn(
	a,
	b,                   // yes
);
{ a: 1, b: 2 };          // no — one line
{
	a: 1,
	b: 2,                // yes
}
function f( a, b ) {}    // yes — JS parameters may
```

## Finder

PHP only. Same command on Windows (PowerShell or cmd), macOS, and Linux. PHP 7.4+ on `PATH`. Reports; does not rewrite.

```
php .cursor/skills/trailing-comma/scripts/find-missing.php path/to/file.php
php .cursor/skills/trailing-comma/scripts/find-missing.php inc
```

Output: `path:line: missing trailing comma before )` (or `]`). Line is the last argument. Exit 1 if any hit. A docblock or comment with a newline after `(` counts as multiline.

Scan tracked PHP (not `.git`, `.local`, `vendor`, `node_modules`), then fix. Leave JS to the same rule while editing; the finder does not parse JS.
