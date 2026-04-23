---
description: "Use when editing README.md, readme.txt, or other user-facing documentation for this repository. Covers readme wording constraints, WordPress readme format, changelog prose style, and public API naming requirements."
applyTo: "README.md, readme.txt"
---

# Documentation Rules

- Treat `readme.txt` as a WordPress.org plugin readme using WordPress readme syntax, not generic plain text or GitHub Markdown.
- Preserve the existing WordPress readme section markers and formatting conventions in `readme.txt`.
- Treat `readme.txt` changelog entries as TSF release notes, not as generic terse changelog bullets.
- Preserve the existing changelog house style in `readme.txt`: audience-segmented sections such as `For everyone` and `For developers`, narrative summaries for smaller releases, and titled release-note prose plus detailed logs for larger releases.
- Preserve the audience-section order in `readme.txt` changelogs: `For everyone`, then `For translators`, then `For developers`. Omit a section only when it genuinely has nothing to report.
- Treat the `For translators` section as largely standardized when present: keep it brief, formulaic, and limited to translation-file and sentence-update notes unless the release genuinely requires more.
- In `For everyone`, preserve this section order when those sections are present: `Upgraded`, `Added`, `Improved`, `Changed`, `Compatibility`, `Removed`, `Fixed`, `Notes`.
- For major releases, subsections inside `For everyone` may be user-defined, but keep them in alphabetical order.
- In the `Compatibility` section, use a nested list. Top-level items must be bolded as `**Theme: <name>:**` or `**Plugin: <name>:**`. Sub-items must detail the changes directly using standard bullets or ordered lists, without category labels like `Added:` or `Fixed:`.
- In `Fixed` sections, preserve the established prose pattern by usually starting items with `Resolved a <quantifier> issue` or `Resolved a <quantifier> regression`, followed by `where` or `causing` when describing the bug.
- In `For developers`, preserve this section order when those sections are present: `PHP API notes`, `JavaScript API notes`, `Option notes`, `Action notes`, `Filter notes`, then the general sections `Improved` and `Other`.
- Within the non-general developer sections, preserve this subsection order when those subsections are present: `Upgraded`, `Added`, `Improved`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Notes`.
- Preserve the existing nested list and indentation style in `readme.txt` changelogs, including bold category labels and indented follow-up bullets or numbered lists where the current format uses them.
- When copying content from code, such as docblocks, comments, or commit notes, into `readme.txt` or other user-facing docs, preserve the essence verbatim. Only minor prose tweaks for readability are allowed. Do not add details that are not present in the source.
- In the readme, when mentioning a method or property in public classes, use the fully qualified name and then the API function in parentheses, for example `The_SEO_Framework\Admin\SEOBar\Builder::generate_bar()` (`tsf()->admin()->seobar()->generate_bar()`). This ensures users can find the method or property in the codebase and understand how to access it via the public API.
