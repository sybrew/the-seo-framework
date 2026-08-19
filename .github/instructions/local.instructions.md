---
description: "Use when handling local-only workspace guidance, private support materials, related-repository cross-references, remediation, troubleshooting, reproduction, triage, or customer-facing diagnosis that may require code changes."
applyTo: ".local/**"
---

# Local Workspace Rules

- Treat `.local/` as private workspace-only context.
- Do not expose `.local/` details or related-repository context in public-facing instructions or responses unless the user asks for them.
- `.local/.instructions/*.instructions.md` is not auto-injected. Read the applicable files before drafting or revising `.local/` content or customer-facing replies that depend on them.
- Keep related-repository cross-references that should not be public in `.local/.instructions/*.instructions.md`, not in tracked repo instruction files.
- Treat support inquiries as first-class engineering work.
- Support inquiries may require code inspection, reproduction, remediation, or patches.
- Do not assume a support inquiry is explanation-only.
- Diagnose whether the issue is configuration, expected behavior, a defect, or a feature gap before recommending action.
- Fix the cause, not the symptom.
- Prefer the smallest safe remediation that prevents end-user issues.
- If the issue cannot be resolved from current context, gather the missing evidence or ask precise follow-up questions.
