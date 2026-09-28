---
name: instruction-trial
description: >-
  Trials instruction wording on fresh Composer 2.5 agents and shortens it
  until the rule is terse and still produces the intended edit. Use when
  editing, adding, condensing, or testing instruction files, style rules,
  copilot-instructions, .instructions.md, or any rule an agent will follow.
---
# Instruction trial

Write the shortest telegraphic rule that still scores. A passing essay is not done.

## When

Run this when changing what an agent must do in:

- `.github/copilot-instructions.md`
- `.github/instructions/*.instructions.md`
- `.cursor/rules/*.mdc`
- any other instruction or style rule

Skip typos, link fixes, and edits that do not change the required action.

## Rubric first

Write the rubric before launching. One agent passes only when every sample passes. Three of four agents must pass.

Each fixture has:

- One sample the rule must change.
- One sample the rule must leave alone.
- One sample per language the rule claims to cover.

Samples are unmarked and not already corrected. Pattern names inside the rule differ from the sample names. Pasting a pattern name into a sample is a fail.

Do not loosen the rubric after seeing output. A new requirement is a new round.

## Wording

One bullet, or a few short sentences. Name the required tokens. No rationale, no leave-as-written essay, no detail another instruction already implies.

Show a real snippet. Do not use placeholder words such as "one entry" or "..."; agents copy them.

State "requires" for anything that must appear. "With X" is read as optional.

If the rule uses pattern names, add: use the names in the code, not these pattern names.

## Swarm

Four fresh agents, one message, identical prompts. Distinct descriptions only (`Trial 1A` …).

Task tool: `subagent_type` `generalPurpose`, `model` `composer-2.5`, `environment` `local`. Do not set `cloud`. Do not use best-of-n-runner. Do not resume an agent onto a new wording.

`run_in_background` false, unless the session is in Multitask Mode. Then set it true, end the turn, and score when they finish. Do not poll.

The prompt is their entire context. They must not open files, search, or use tools.

```
You are testing whether ONE style rule is clear. Do not open files, search the repo, or use tools. Use only the rule and the samples below.

RULE (the entire style guide you may use):
<candidate text>

Rewrite each sample so it follows that rule. Keep the code unchanged. Do not add rules of your own. If the rule does not say to change something, leave it.

SAMPLE 1
...

Return only the rewritten samples and, if you guessed, one line per guess.
```

Guess lines explain misses. They are not a pass.

## Loop

1. Trial the shortest wording that names every required token.
2. Below 3 of 4: add only the missed constraint. Retest.
3. At 3 of 4 or better: cut a clause the guesses show was unused, or merge two sentences. Retest the same fixture.
4. A cut that drops below 3 of 4 is discarded. Save the previous pass and stop.
5. Stop after three expansions that do not gain a pass, and say which clause is still ambiguous.

Known misses:

- "With X" treated as optional. Say "requires".
- Placeholder words in the rule copied into the sample.
- Pattern names pasted onto the sample.
- The negative sample edited the same way as the positive one.
- A second language or format skipped, or given the first format's syntax.
- An example attached to the wrong name, or split off its tag.

## Save

Write the instruction file only after the kept wording passes. Do not leave a failing draft in the file.

Report the score, the miss that each failed round shared, and the wording saved. Link each trial as `[Trial NA](id)`.
