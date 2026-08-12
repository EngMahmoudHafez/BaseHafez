# Upgrading a project built from this base

This repository is a reusable **base** (a GitHub template). A new project starts by generating
from the template — or cloning — and then keeps receiving base improvements, **especially
security fixes**, through a git `upstream` remote instead of drifting into a private fork that
never sees them again.

## One-time setup in the downstream project

```bash
git remote add upstream https://github.com/EngMahmoudHafez/BaseHafez.git
git fetch upstream --tags
```

## Pulling a base release

```bash
git fetch upstream --tags
git merge vX.Y.Z          # or: git cherry-pick <commit> for a single hotfix
# resolve conflicts, then re-run the gate
composer install
npm ci
php artisan migrate
composer check            # Pint, Larastan, tests, audit
php artisan base:doctor   # sanity-check the environment
```

Merge **PATCH** releases (security and bug fixes) promptly. Review **MINOR** and **MAJOR**
releases against the changelog before merging.

## Versioning policy

The base uses [Semantic Versioning](https://semver.org/):

| Bump | Meaning |
| --- | --- |
| **MAJOR** | A breaking change: a changed module contract, a removed or renamed public surface, or a release that requires a manual migration step. |
| **MINOR** | A new, backward-compatible capability — a new command, an optional preset, a new dashboard component. |
| **PATCH** | Security and bug fixes with no API change. |

Every release is tagged and described in [CHANGELOG.md](../CHANGELOG.md); breaking changes carry
migration notes in their changelog entry.

## Cutting a release (maintainers)

1. Move the `Unreleased` section of `CHANGELOG.md` under a new `## [X.Y.Z] - YYYY-MM-DD` heading.
2. Commit, then tag: `git tag -a vX.Y.Z -m "vX.Y.Z"` and `git push origin vX.Y.Z`.
3. Announce the release so downstream projects know to merge it — prioritise any PATCH that
   contains a security fix.

## Why not clone-and-detach?

If every project detaches from the base, one security fix has to be re-applied by hand in every
copy. Tracking `upstream` keeps a single, auditable source of fixes that each project merges on
its own schedule.
