# Foundation architecture

## Supported modules

| Module | Owns | Must not own |
| --- | --- | --- |
| `Auth` | API authentication, profiles, dashboard login, users, staff, roles | Analytics or product-specific learning data |
| `Base` | module discovery, repository primitives, shared HTTP/UI helpers, dashboard layout | Complete business screens |
| `Notifications` | polymorphic database notifications, device tokens, push/broadcast delivery, and cleanup | Course, meeting, payment, or scheduling workflows |
| `Structure` | editable public-site structure and contact messages | Authentication or notification delivery |
| `Settings` | generic key/value configuration (`get`/`set`, cached) + dashboard editor | Project-specific business values (seed those in the project, not the base) |

The directory `app/Modules` is the source of truth. A removed module is absent everywhere: runtime code, routes, schedules, menu entries, permissions, seeders, translations, configuration, and tests.

## Canonical module layout

```text
app/Modules/<Module>/
├── Http/
│   ├── Controllers/Api/V1
│   ├── Controllers/Dashboard
│   ├── Requests/Api/V1
│   ├── Requests/Dashboard
│   ├── Resources/V1
│   └── Services/Api/V1|Dashboard
├── Models
├── Providers
├── Repositories
│   └── Eloquent
├── Resources/views
├── Routes/api/v1/web.php
├── Routes/dashboard/dashboard.php
└── database/migrations|factories|seeders
```

Do not add compatibility copies in older locations. Move a surface, update every reference, then run the reference sweep.

## Request flow

```text
Route → Controller → FormRequest/Policy → Workflow Service → Repository/Model
                                      ↘ API Resource or owning Blade view
```

- Controllers translate HTTP input/output and remain small.
- Form Requests validate and normalize request data.
- Policies or route/controller middleware authorize the operation.
- Services coordinate a complete workflow and transactions.
- Repositories isolate reusable persistence queries; one-off Eloquent reads do not require a speculative repository method.
- API Resources define public response contracts.

## Discovery and boot

`App\Support\ModuleDiscovery` discovers canonical module service providers and database seeders. `ModuleServiceProvider` loads only canonical API routes, dashboard routes, migrations, views, translations, and console commands. `RepositoryServiceProvider` binds `Repositories/*Interface.php` to `Repositories/Eloquent/*Repository.php` by matching names.

If a provider must be registered manually, declare `public const AUTO_DISCOVER = false` and add it explicitly to `bootstrap/providers.php`.

## Persistence rules

- Migrations and seeders stay in the owning module.
- Seed only deterministic foundation data; demo/product data requires an explicit spec.
- Seeders are idempotent and non-destructive. The optional first manager is sourced from `BASE_ADMIN_*`; committed credentials are forbidden.
- Wrap multi-write workflows in a transaction.
- Store a replacement file before deleting the previous file, and clean up the new file if the database write fails.
- Tests must use a disposable database. The committed PHPUnit configuration uses in-memory SQLite and contains no credentials.

## Public boundaries

Auth API compatibility is protected by contract and feature tests. Notifications exposes generic user notification behavior only. A future feature module may call Notifications, but Notifications may not import that feature's models.

User profiles require an authenticated API user. Password recovery returns the same outward response for known and unknown email addresses. OTP records are scoped to a user, purpose, and channel; their codes are hashed, expiring, attempt-limited, and single-use. Reset tokens are separately hashed in the configured cache and consumed after a successful reset.

## Shared conventions

Base-wide conventions (established 2026-08). Follow them for new code; cross-cutting shared traits live in `App\Modules\Base\Concerns`, while a module-specific concern lives in its owning module (e.g. `App\Modules\Notifications\Concerns\HasDeviceTokens`).

- **API responses** — one shape, one source: `App\Modules\Base\Http\Responses\ApiResponse::success($status, $message, $data)` / `::error(...)`. It normalizes `JsonResource`, `ResourceCollection`, and `LengthAwarePaginator` into `{status, message, data, pagination?}`. Do not build ad-hoc JSON envelopes.
- **API resources** — each model exposes `XResource` (full record) and `XSummaryResource` (id/name/photo… for lists and embedding). Shared summaries (`UserSummaryResource`, `ManagerSummaryResource`) are reused wherever that model appears.
- **Files & images** — `InteractsWithFiles` is the single file concern: generic `storeFile` / `storeImage` / `replaceImage` / `safeDeleteFiles` for Services and Repositories, plus model sugar `putImage($file)` / `image_url` / `deleteImage()` (column defaults to `avatar`, files land under `{plural model}/{column}`; override `imageColumn()` / `imageFolder()`). There is exactly one `optimizeImage()` in the codebase — it lives here.
- **Dashboard lists** — filtering comes from `HandlesResourceQuery::applyDashboardFilters(...)`: a service declares `searchable`/`filterable` and the trait builds the query.
- **Notifications** — `App\Modules\Notifications\Services\PushNotifier::send($notifiable, $title, $body, $data)` writes the DB notification, pushes via FCM, and broadcasts (env-gated). See `docs/notifications.md`.
- **Editable site content** — `Structure` is a section registry: define a section once in `app/Modules/Structure/Support/sections.php` and its editor, validation, routes, and serialization follow. No per-section controller/request/view.
