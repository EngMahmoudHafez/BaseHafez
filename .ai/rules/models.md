---
paths:
  - app/Modules/**/Models/**
---

# Models

- Declare `$fillable` explicitly (preferred) — never leave a model mass-assignable by an empty
  `$guarded = []` when it accepts request input.
- Define casts in the `casts()` method (Laravel 11+ style), not the legacy `$casts` property.
- Model code is limited to Eloquent concerns: relationships, scopes, accessors/mutators, casts, and
  events. Business workflows belong in a Service, reusable queries in a Repository.
- Type-hint relationship return types (`HasMany`, `BelongsTo`, …). Name query scopes `scopeXxx`.
- Do not instantiate a model from inside `boot()` / `boot*()` trait methods — Laravel 13 throws a
  `LogicException` for nested instantiation during booting.
- Hash secrets with the `hashed` cast or explicit hashing; never store plaintext credentials or OTPs.
- Keep factory definitions in `database/factories`; do not embed seed/demo data in the model.

## Annotate models with @property; resources with @mixin
Larastan here has NO schema scan (databaseMigrationsPath is intentionally empty — enabling it makes Larastan read raw column types and ignore casts(), which breaks enum comparisons). So EVERY new Eloquent model MUST carry a `@property` docblock listing its columns with runtime types (enum-cast columns as the enum e.g. `UserStatus $status`; date columns as `\Illuminate\Support\Carbon|null`; json/array casts as `array<...>`; accessors as `@property-read`; relations as `@property-read Model` / `Collection<int, Model>`). API Resources (JsonResource) that access `$this->column` MUST add `@mixin \App\Modules\...\Models\X` so Larastan proxies to the model. Without these, Larastan flags every `$model->column` as an undefined property and the PHPStan baseline regrows. The baseline was burned 123→23 this way; the remaining 23 are pre-existing repository return-type generics + minor over-precision.
