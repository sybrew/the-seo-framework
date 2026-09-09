---
description: "Use when editing PHP, WordPress PHP, SQL-in-PHP, or mixed PHP and HTML template files in this repository. Covers TSF PHP style, namespace rules, SQL formatting, and template formatting."
applyTo: "**/*.php"
---

## Repository-Specific Rules

- `inc\classes\front` classes should never be used/imported for admin-facing classes or views. Likewise, `inc\classes\admin` classes should never be used/imported for front-facing classes or views. If either happens, we may need to introduce a helper function that serves both the front and admin sides.

# PHP and WordPress Rules

- Use PHP 7.4+.
- Use WordPress coding standards, except where this file narrows them.
- Write PHP attributes inline on the symbol they annotate, for example `function foo( #[\SensitiveParameter] $x )`. If the attribute plus that symbol would exceed 80 visual columns (tab = 4), put the attribute on the line above. If the attribute itself exceeds 80, wrap its arguments like a function call.
- Never add phpcs comments. That includes `phpcs:disable`, `phpcs:enable`, `phpcs:ignore`, `phpcs:set`, and any `-- phpcs:` annotation. Do not copy them from existing files into new or edited code. Existing comments in unmodified files stay.
- Avoid functions `wp_sprintf`, except with `%l` lists, `wp_json_encode`, and `status_header`.
- Never add hooks in class constructs.
- In `add_filter()` and `add_action()`, write each argument on a new line when implementing anonymous functions.
- Do not mark anonymous functions or arrow functions `static` except in instance methods. `static` only prevents binding `$this`.
- Do not create `validate_callbacks` for REST routes. Validate and sanitize parameters directly in the route callback.
- Use short array syntax.
- Add trailing commas on multiline array items and function calls. Do not add them on function, method, closure, or `fn()` parameter lists, closure `use (` lists, or control-structure conditions (PHP 8.0).
- Never use strict typing unless required.
- Short Echo Tags, HereDoc, and NowDoc are permitted.
- Use `(s|v)printf` for complex strings when variables still need to be escaped.
- Align array key/value separators with spaces before the separator.
- Do not pad array access strings with spaces.
- Avoid output buffering.
- You may use `str_starts_with`, `str_ends_with`, and `str_contains`; WordPress provides these.
- Refrain from colon syntax for conditionals and loops.
- You may use logical operators like `and`, `or`, and `xor`, but not in conditional expressions.
- Use `??` (or `??=`) when the coalesced value is assigned or returned. The miss value must be `null`.
- Use `?:` when any empty value is a miss and a string of `0` is not a valid keep.
- Use `or` / `and` for statement-level short-circuit, for example `isset( $x ) or $x = ...` or `get_cache() or set_cache()`. Do not use `??` as a statement.
- Pass-through wrappers stay on the opener line; the inner call whose arguments exceed 30 characters starts on the next line. After the coalesce or ternary break, a remaining single nested call may stay compact, for example `?? Query\Cache::memo( self::is_menu_page( Admin\Menu::get_page_hook_name() ) )`.
- Keep a coalesce of two wrappers on one line when the right-hand call is a single-argument pass-through, for example `memo() ?? memo( array_values(`.
- When a `?:` expression wraps, treat it like `??`: break after `=`, break at `?:` first, and pad the first operand by 3 spaces.
- A single-property array that is the only argument stays on the opener, for example `get_post_types( [ 'public' => true ] )`. That is not the multi-argument wrap trigger. If the only argument is a multi-property array, keep `fn( [` and wrap the properties.
- Wrap a Boolean `&&` / `||` function argument so phpcs treats it as one argument.
- Compact `if` / `elseif`: keep a void `return;`, `continue;`, or `break;` on the same line when that line is at most 80 columns (tab = 4). Put a `return` with a value on the next line, unbraced. If that compact void line exceeds 80, put the terminator on the next line, unbraced. Do not wrap a single expression (no `&&` / `||`) just to make room for the terminator. If an `&&` / `||` condition line exceeds 80, wrap at those operators (first operand padded by 3 spaces) and use braces. Do not wrap only the `return`.
- After a function opening `{`, insert a blank line when the body has two or more statements. A single-statement body stays tight. A leading comment is not a statement: comment plus a single `return` or `yield` stays tight, comment on the first interior line. With two or more statements, the blank line comes first so that comment sits on the next interior line. Do not insert that blank after `if`, `elseif`, `else`, loop, `try`, or labeled `{` (`label: {`) braces.
- A `return` or `yield` that ends that interior always has a blank line before it if that interior has two or more statements. Early `return` / `yield` inside `if` / `else` count: the blank is a visual split that this path ends there. A single-statement interior that is only a `return` or `yield` stays tight. Own-line comments immediately above that `return` or `yield` stay attached to it; the blank line goes before that comment group. Do not insert that blank before `continue` or `break`. After an early `return`, `continue`, or `break` guard, insert a blank line before the next sibling statement, including another guard.
- Inside a function, isolate a required in-body docblock from sibling statements: a blank line before the docblock and a blank line after the statement it documents. Keep the docblock glued to that expression. Consecutive documented units share one blank between them. The function-opening blank already counts as the before-blank. Do not add a blank before `}`. This is for WordPress hook documentation on sibling `do_action`, `apply_filters`, `do_action_ref_array`, `apply_filters_ref_array`, `do_action_deprecated`, and `apply_filters_deprecated`. Glue the docblock to that call. An `if` or `return` may carry the doc when that hook is the entire condition or the returned expression. If the hook is an operand of a compound `if`, keep that one `if` (cheap tests first so the hook still short-circuits). Put the doc immediately above the call, grouping the call in parentheses when `!` or a line-start `&&` / `||` would otherwise sit between the doc and the call. Do not split the `if` into extra guards to isolate the doc. It is not for undocumented hook calls. It is not for a hook doc that annotates a function argument: glue that docblock to that argument; argument wrap already isolates it. It is not for `/* translators: */`, which stays immediately above the gettext call, including when that string is an argument. It is not for other interior `/**` (closure parameter docs, explanatory comments).
- When outside global namespace:
	1. Namespace-escape only these native PHP functions: `strlen`, `is_null`, `is_bool`, `is_long`, `is_int`, `is_integer`, `is_float`, `is_double`, `is_string`, `is_array`, `is_object`, `is_resource`, `is_scalar`, `boolval`, `intval`, `floatval`, `doubleval`, `strval`, `defined`, `chr`, `ord`, `call_user_func_array`, `call_user_func`, `in_array`, `count`, `sizeof`, `get_class`, `get_called_class`, `gettype`, `func_num_args`, `func_get_args`, `array_slice`, `array_key_exists`, `sprintf`, `constant`, `function_exists`, `is_callable`, `extension_loaded`, `dirname`, `define`.
	2. Do not namespace-escape any other native PHP functions.
	3. Put imported non-native PHP symbols above the copyright header, below the direct-access guard, in this order: constants, functions, classes.
	4. Namespace-escape imported non-native PHP function calls that resolve outside the current namespace.
	5. Namespace-escape constants that are not imported.
	6. When importing multiple symbols from the same namespace, use a single import statement with a comma-separated list and each item on a new line. A single symbol stays on one line: `use The_SEO_Framework\Data;` Do not wrap a lone name in `{ }`.
	7. Mix depths in one group unless two or more symbols share the same parent namespace. Then split that parent into its own group: `use The_SEO_Framework\{ Data, Helper\Format, Helper\Query };` becomes `use The_SEO_Framework\Data;` and `use The_SEO_Framework\Helper\{ Format, Query };`. A lone nested name stays mixed: `use The_SEO_Framework\{ Data, Helper\Query };`.
	8. Within each group (constants, functions, classes), sort imported identifiers alphabetically (case-sensitive `strcmp`). `API` before `Admin`.
	9. Import the prefix you write at the call site. A unique leaf is the import (`HTML::`). Import a parent when that parent is a class you call, or when the leaf name is already used (`Format\HTML::`, `Data\Plugin::`, `Query\Utils::`). Same-namespace siblings stay unqualified.
	10. Do not namespace-escape calls to functions defined in the current namespace unless phpcs.xml already requires an exception.
- For SQL queries over 80 characters:
	1. Put every clause on a new line.
	2. If a clause exceeds 60 characters, put each logical operator on a new line, put each predicate on a new line, keep aliases on the same line as the column unless they contain an operator or exceed 60 characters, put expressions on new lines, and indent every new line by one tab.
	3. Put operators at the start of new lines.
- When mixing PHP and HTML, indent HTML to match the PHP block scope.
- Close PHP tags on a new line after an opening brace.
- Reopen PHP tags on their own line before the closing brace.
- Use double quotes for HTML attribute values.
- Do not self-close void elements.
- Do not use quote marks on literal string attribute values unless necessary.
