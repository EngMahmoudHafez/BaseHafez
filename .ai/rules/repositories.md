---
paths:
  - app/Modules/**/Repositories/**
---

# Repositories

Reference base contracts: `app/Modules/Base/Repositories/{RepositoryInterface,ReadableRepositoryInterface,PaginatableRepositoryInterface}.php`.

- A repository isolates **reusable** persistence queries. Do not add a repository method to wrap a
  single one-off read — call the model directly from the service in that case.
- Declare an interface in `Repositories/` and its implementation in `Repositories/Eloquent/`.
  `RepositoryServiceProvider` binds `*Interface.php` to `Eloquent/*Repository.php` by matching names,
  so keep the names aligned (e.g. `UserRepositoryInterface` ↔ `Eloquent/UserRepository`).
- Extend the shared Base contracts instead of re-declaring `find`, `paginate`, etc.
- Return Eloquent models/collections or paginators; do not return view or HTTP types from a repository.
- Apply eager loading (`with`) here so callers cannot cause N+1s. Never accept raw user SQL; bind
  parameters and constrain by column.
- Repositories do not run transactions spanning multiple aggregates — that is the service's job.
