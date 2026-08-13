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

## Base Repository is generic — extend it with @extends Repository&lt;Model&gt;
`Base\Repositories\Eloquent\Repository` is `@template TModel of Model`. Every concrete repository MUST declare `@extends Repository<ConcreteModel>` on the class — this is what makes inherited `create()/getById()/getAll()/query()` return the concrete type instead of `Model` (and keeps PHPStan green with no baseline). Larastan can't propagate the free template through Eloquent's `newQuery()/get()` inside the base itself, so the base's `query()` and the three collection reads carry an inline `@var Builder<TModel>` / `Collection<int, TModel>` — leave those; subclasses need no casts. When an interface must expose a concrete return type (e.g. `create(): User`), narrow it in the *interface* and add a one-line `return parent::...()` override in the concrete repo (the interface's native return type forces the override). Don't redeclare `protected Model $model` in subclasses — it shadows the base `@var TModel` and reintroduces `Model|null` return errors.
