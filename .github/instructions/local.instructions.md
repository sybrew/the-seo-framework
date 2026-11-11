---
description: "Use when handling local-only workspace guidance, private support materials, related-repository cross-references, remediation, troubleshooting, reproduction, triage, or customer-facing diagnosis that may require code changes."
applyTo: ".local/**"
---

# Local Workspace Rules

- `.local/` is gitignored. Use explicit paths for authorized workspace material.
- Treat `.local/` as private workspace-only context.
- Do not expose `.local/` details or related-repository context in public-facing instructions or responses unless the user asks for them.
- Keep related-repository cross-references that should not be public in `.local/.instructions/*.instructions.md`, not in tracked repo instruction files.
- Treat support inquiries as first-class engineering work.
- Support inquiries may require code inspection, reproduction, remediation, or patches.
- Do not assume a support inquiry is explanation-only.
- Diagnose whether the issue is configuration, expected behavior, a defect, or a feature gap before recommending action.
- Fix the cause, not the symptom.
- Prefer the smallest safe remediation that prevents end-user issues.
- If the issue cannot be resolved from current context, gather the missing evidence or ask precise follow-up questions.

## Load `.local` instruction files

Select workspace instructions for the task at hand. Use these commands only when `.local` and `.local/.instructions` are ordinary directories. Skip links and reparse points.

1. From the repo root, extract YAML frontmatter only (commands below).
2. Read the files whose `applyTo` matches the current paths, or whose `description` matches the task. Files with no `applyTo` still match on `description`.
3. Do not paste the extract or private bodies into chat.

### Windows

```powershell
Get-ChildItem -LiteralPath '.local/.instructions' -Filter '*.instructions.md' -File |
	Where-Object { -not ($_.Attributes -band [IO.FileAttributes]::ReparsePoint) } | ForEach-Object {
	$raw = [System.IO.File]::ReadAllText($_.FullName)
	if ($raw -match '(?s)\A---\r?\n(.+?)\r?\n---') {
		Write-Output ("FILE: {0}`n{1}`n" -f $_.Name, $Matches[1].Trim())
	}
}
```

### macOS / Linux

```bash
for f in .local/.instructions/*.instructions.md; do
	[ -f "$f" ] && [ ! -L "$f" ] || continue
	echo "FILE: ${f##*/}"
	awk 'BEGIN{c=0} /^---$/ {c++; if(c==1) next; if(c==2) exit} c==1' "$f"
	echo
done
```
