---
name: normalize-line-endings
description: >-
  Normalize text file line endings to LF on Windows after agent writes. Use when
  creating or editing tracked text files on Windows, when CRLF/LF endings are
  mentioned, or after Write/StrReplace on paths covered by .editorconfig or
  .gitattributes eol=lf rules.
---
# Normalize Line Endings to LF

On Windows, agent `Write` and `StrReplace` tools often persist `\r\n` even when content uses `\n`. This repo requires LF via `.editorconfig` (`end_of_line = lf`) and `.gitattributes` (`*.md`, `*.mdc`, `*.txt`, etc. `eol=lf`).

## When to run

After creating or editing any text file that must use LF created via `Write` or `StrReplace`, run normalization before finishing the task. Do this even when only one file changed. Then append a row to `../../.local/skills/tally/normalize-line-endings.md` from the script `RESULT` line.

## Command

Run from the repo root. Paths below are relative to it.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ".cursor\skills\normalize-line-endings\scripts\normalize-lf.ps1" ".cursor\rules\core.mdc" "lib\js\title.js"
```

If cwd may not be the repo root, resolve it first:

```powershell
Set-Location (git rev-parse --show-toplevel)
powershell -NoProfile -ExecutionPolicy Bypass -File ".cursor\skills\normalize-line-endings\scripts\normalize-lf.ps1" ".cursor\rules\core.mdc"
```

To normalize every matching file under a directory:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ".cursor\skills\normalize-line-endings\scripts\normalize-lf.ps1" -Recurse ".cursor"
```

## Verify (optional)

```powershell
python -c "from pathlib import Path; p=Path(r'PATH'); b=p.read_bytes(); print(p.name, 'CRLF' if b'\r\n' in b else 'LF')"
```

## Tally

After each run, append a row to `../../.local/skills/tally/normalize-line-endings.md` (create it if missing).

## Rules

- Do not "fix" encoding issues beyond CRLF→LF and ensuring a final newline.
- Preserve UTF-8 without BOM unless the file already used BOM.
- Do not normalize binary files.
