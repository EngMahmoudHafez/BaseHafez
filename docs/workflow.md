# Working on the base

## Starting a feature

1. Read `AGENTS.md` and the current files under `specs/`.
2. Create `specs/<number>-<feature>/spec.md`, `plan.md`, and `tasks.md` when behavior or architecture changes.
3. Search for the existing owner before creating a class. Extend the current surface instead of duplicating it.
4. Implement one complete vertical slice with validation, authorization, persistence, response/view, route, and tests.
5. Run focused tests during development and the full gates before handoff.

## Adding a durable rule

Add recurring project decisions under `Persistent project decisions` in `AGENTS.md` using:

```text
- YYYY-MM-DD: <rule>; <reason>.
```

If the rule is mechanically checkable, add or update an architecture test in the same change. If it changes behavior or scope, update the active specification. If it changes dashboard presentation, update the dashboard design document.

Do not put one-off task notes in `AGENTS.md`; keep them in the matching specification or audit report.

## Adding a module

```bash
php artisan make:module ModuleName
php artisan make:module-model ModuleName ModelName --all
```

Then define business fields and authorization, register explicit routes, add translated menu entries only when a route exists, and test repository bindings plus public behavior.

## Removing a feature

Remove the complete slice: classes, provider, routes, schedules, migrations only when data retirement is intentional, seeders, permissions, menu items, configuration, translations, assets, tests, and documentation. Search both the class name and namespace after the edit.

## Changing dependencies

Change Composer or npm manifests and their lock files together using the package manager. Never hand-edit a lock file. If registry access is unavailable, keep the manifest/lock pair consistent and record the candidate removal in the audit report for the next connected maintenance window.

## Seeding a new project

Foundation seeders must be safe to run repeatedly. Use `firstOrCreate`, `updateOrCreate`, or a documented synchronization step; never truncate shared data. An initial privileged account must come from explicit environment values or an interactive command and must never use a password committed to the repository.

## Handoff checklist

```bash
composer check              # Pint (PHP) --test, Larastan, tests, composer audit
composer validate --strict
vendor/bin/phpstan analyse
php artisan route:list
php artisan view:cache
npm audit
npm run build
git diff --check
```

Finish with a `laravel-guard` review, then a `clean-code-guard` review, of the production diff (and
`test-guard` / `docs-guard` when tests or docs changed). Report any environment limitation explicitly;
for example, if `pdo_sqlite` is unavailable locally, rely on CI rather than calling the suite green.
Never call a check successful when it did not run.
