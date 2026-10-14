---
name: copyright
description: >-
  Updates copyright years on files changed since the start of the current year.
  Use when the user asks to update copyright headers, refresh the copyright
  year, or before a release. Only touches files that changed.
---
# Copyright year

Updates `@copyright` and `Copyright (C)` headers only. Holder text stays.

- A single year lower than the current year becomes `YEAR - <current year>`.
- A range whose end year is lower than the current year gets that end year set to the current year.
- A header already at the current year stays.

Only tracked files with a commit since 1 January of the current year, or since `-SinceTag`. Do not edit `*.min.js` or `*.min.css`. Do not add a header where none exists.

Dry run is the default. When the user asks to update, run `-Apply`.

```
powershell -NoProfile -File .cursor/skills/copyright/scripts/update-copyright-year.ps1
powershell -NoProfile -File .cursor/skills/copyright/scripts/update-copyright-year.ps1 -Apply
powershell -NoProfile -File .cursor/skills/copyright/scripts/update-copyright-year.ps1 -SinceTag v5.1.0 -Apply
```
