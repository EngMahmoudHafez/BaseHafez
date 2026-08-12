---
paths:
  - app/**
---

# Architecture

The full contract lives in `docs/architecture.md`. `app/Modules` is the source of truth and is
**meant to grow** — this is a base for building on. `FoundationArchitectureTest` enforces the
module *contract*, not a fixed list of names: any module is allowed as long as it is StudlyCase,
ships a discoverable service provider (so it actually loads), and keeps every surface on its
canonical path. A module directory that is present but not loaded fails the test loudly. Run
`php artisan base:modules` to see what is discovered and `php artisan base:doctor` to check it.

- Follow the request flow: Route → Controller → FormRequest + permission/policy → Service → Repository/Model, returning an API Resource or the owning Blade view.
- Keep each surface on its canonical path: `Http/Controllers/{Api/V1,Dashboard}`, `Http/Requests/{Api/V1,Dashboard}`, `Http/Resources/V1`, `Http/Services/{Api/V1,Dashboard}`, `Models`, `Repositories/Eloquent`, `Routes/api/v1/web.php`, `Routes/dashboard/dashboard.php`, `database/{migrations,factories,seeders}`.
- A module owns its complete slice (routes, migrations, seeders, views, translations, permissions, tests). `Base` owns shared plumbing only; it must not own a complete business dashboard screen.
- Do not create a class before searching for its existing owner. Extend the current surface instead of duplicating it. After moving a surface, `git grep` the class name **and** namespace.
- Prefer a Laravel-native API over custom code. Do not add a service, DTO, or repository method to wrap a single Eloquent call.
- Register providers through `App\Support\ModuleDiscovery`. If a provider must be manual, set `public const AUTO_DISCOVER = false` and add it to `bootstrap/providers.php`.
