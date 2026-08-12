# Portable Laravel review skills

Three read-only, version-aware review skills that work on **any** Laravel project. They report
findings; they never rewrite code.

| Skill | Answers | Run it |
| --- | --- | --- |
| **laravel-guard** | "Is this diff correct and on-convention?" | after writing code, before commit/PR |
| **laravel-modernizer** | "Does the framework already provide this?" | before a refactor, to cut custom code |
| **blade-dashboard-review** | "Does this screen follow the design system?" | after editing a dashboard view |

These are the generic, shareable versions. The checklists are universal Laravel; the project-specific
parts are marked with `<…>` placeholders you fill in once.

## Install

Copy the three folders into the target project's Claude Code skills directory:

```bash
cp -r laravel-guard laravel-modernizer blade-dashboard-review <target-project>/.claude/skills/
```

Each skill is a folder containing a single `SKILL.md`. Claude Code discovers them automatically; invoke
one with `/laravel-guard` (etc.) or let it activate by description.

## Adapt (required, ~10 minutes)

Open each `SKILL.md` and replace every `<…>` placeholder with your project's real values:

| Placeholder | Set it to |
| --- | --- |
| app source path | `app/`, or `app/Modules/**` for a modular monolith |
| convention docs | `AGENTS.md`, `.ai/rules/*`, `CONTRIBUTING.md` — whatever records your rules |
| shared abstractions | your response wrapper, API-resource pattern, file/image trait, query-filter helper |
| UI stack + components | Bootstrap or Tailwind; your `x-…` component namespace; your design-system doc |
| reference screens | one or two exemplar screens reviewers should copy |
| gate commands | your `pint` / `phpstan` / test commands |

Only the placeholders are project-specific — the review logic underneath is the same everywhere.

## Prerequisites

- **Recommended:** [Laravel Boost](https://github.com/laravel/boost) MCP server, so the skills can
  version-check an API with `search-docs` against your installed versions. Without it, they fall back
  to the versioned docs for your `composer.json`.
- Nothing else. The skills are read-only and add no dependencies.

## Design principles

- **Version-aware.** Every skill confirms an API exists in your *installed* versions before proposing
  it — so they stay correct across upgrades.
- **Read-only.** They produce ranked findings and wait; they never edit code on their own.
- **No taste-only churn.** "Newer" is never a reason by itself; a change must reduce complexity,
  improve correctness/readability, or remove duplication.

Written for modern Laravel (10–13). The version-check rule keeps them accurate as the framework moves.
