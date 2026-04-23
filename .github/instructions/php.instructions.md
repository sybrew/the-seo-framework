---
description: "Use when editing PHP, WordPress PHP, SQL-in-PHP, or mixed PHP and HTML template files in this repository. Covers TSF PHP style, namespace rules, SQL formatting, and template formatting."
applyTo: "**/*.php"
---

# PHP and WordPress Rules

- Use PHP 7.4+.
- Use WordPress coding standards, except where this file narrows them.
- Avoid functions `wp_sprintf`, except with `%l` lists, `wp_json_encode`, and `status_header`.
- Never add hooks in class constructs.
- In `add_filter()` and `add_action()`, write each argument on a new line when implementing anonymous functions.
- Do not create `validate_callbacks` for REST routes. Validate and sanitize parameters directly in the route callback.
- Use short array syntax.
- Never use strict typing unless required.
- Short Echo Tags, HereDoc, and NowDoc are permitted.
- Use `(s|v)printf` for complex strings when variables still need to be escaped.
- Align array key/value separators with spaces before the separator.
- Do not pad array access strings with spaces.
- Avoid output buffering.
- You may use `str_starts_with`, `str_ends_with`, and `str_contains`; WordPress provides these.
- Refrain from colon syntax for conditionals and loops.
- You may use logical operators like `and`, `or`, and `xor`, but not in conditional expressions.
- When outside global namespace:
	1. Namespace-escape only these native PHP functions: `strlen`, `is_null`, `is_bool`, `is_long`, `is_int`, `is_integer`, `is_float`, `is_double`, `is_string`, `is_array`, `is_object`, `is_resource`, `is_scalar`, `boolval`, `intval`, `floatval`, `doubleval`, `strval`, `defined`, `chr`, `ord`, `call_user_func_array`, `call_user_func`, `in_array`, `count`, `sizeof`, `get_class`, `get_called_class`, `gettype`, `func_num_args`, `func_get_args`, `array_slice`, `array_key_exists`, `sprintf`, `constant`, `function_exists`, `is_callable`, `extension_loaded`, `dirname`, `define`.
	2. Do not namespace-escape any other native PHP functions.
	3. Put imported non-native PHP symbols above the copyright header, below the direct-access guard, in this order: constants, functions, classes.
	4. Namespace-escape imported non-native PHP function calls that resolve outside the current namespace.
	5. Namespace-escape constants that are not imported.
	6. When importing multiple symbols, use a single import statement with a comma-separated list and each item on a new line.
	7. Do not namespace-escape calls to functions defined in the current namespace unless phpcs.xml already requires an exception.
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
