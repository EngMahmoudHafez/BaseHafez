---
paths:
  - app/Modules/**/database/**
---

# Database (migrations, factories, seeders)

- Migrations, factories, and seeders stay in the owning module under `database/`.
- Seeders are **idempotent and non-destructive**: use `firstOrCreate` / `updateOrCreate`; never
  `truncate()` shared data. `FoundationArchitectureTest` fails on `truncate(` in a seeder.
- **No fixed passwords or secrets in seeders** — the test rejects `bcrypt('literal')`,
  `Hash::make('literal')`, and `'password' => 'literal'`. The optional first admin comes from
  `BASE_ADMIN_*` env only.
- Seed only deterministic foundation data. Demo/product data requires an explicit spec.
- Add indexes and foreign keys for every column you filter or join on. Give each migration a reversible
  `down()`.
- Prefer `upsert()` with a non-empty `uniqueBy` — Laravel 13 throws `InvalidArgumentException` on an
  empty `uniqueBy`.
- Change a manifest and its lock together via the package manager; never hand-edit a lock file.
