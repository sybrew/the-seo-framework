---
name: makepot
description: >-
  Regenerates language/autodescription.pot via makepot-tsf (WP-CLI i18n
  make-pot, no WordPress). Use only when the user asks to generate the POT,
  typically before a release. Do not hand-edit the POT.
disable-model-invocation: true
---
# Generate language/autodescription.pot

Run only when the user asks. Typically before a release. Do not run after ordinary string edits.

Never write `language/autodescription.pot` by hand. Never search this repo for grunt-wp-i18n or wp-cli.phar.

## Permission

Read `.local/pot/permission.txt` with an explicit path before cloning the engine. Grep/Glob cannot see `.local/`.

Never invent `True` or `False`. Never write this file unless the user has replied with the flag.

Format is always one line (`True` or `False`, never guessed):

```
POT=True|False
```

- If the file does not exist, prompt. If you cannot prompt and no permissions exist, skip POT generation altogether and ask at the end of the chat.
- After the user replies with the flag, create the file from that reply.
- If the flag is `False`, do not prompt again, and do not clone or generate the POT.
- If the flag is `True`, proceed. After a permitted import, keep that flag `True`.

The makepot script reads this file. A missing file is a hard stop. A `False` flag skips generation even if the engine is already cloned.

## Engine

Public source of truth:

- https://github.com/theseoframework/makepot-tsf → `.local/pot/makepot-tsf`

If permission is `True` and `run.php` is missing, clone that repo and run `composer install` in it. Do not copy the engine from elsewhere; clone the public repo above.

Override the path with `MAKEPOT_TSF_DIR` only when the user sets it.

## Command

From the repo root:

```
php .cursor/skills/makepot/scripts/makepot.php
```

If the script exits non-zero, fix the failure. Do not hand-write the POT as a fallback.
