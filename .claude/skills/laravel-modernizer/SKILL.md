---
name: laravel-modernizer
description: Propose version-native Laravel refactors for existing code (read-only). Use when asked to modernize a file/module or find where the code reinvents something Laravel 13 provides natively. It searches the installed-version docs and reports ranked suggestions — it does NOT modify code until you approve specific items.
---

# Laravel Modernizer

Finds places where existing code is more verbose or custom than the Laravel-native equivalent for the
**installed** versions, and proposes replacements. It answers "is there a newer/better Laravel API?",
which is different from `laravel-guard` ("is the current code correct?").

## When to use

- "Use laravel-modernizer on app/Modules/Auth/Http/Controllers" — audit a path for modernization.
- Before a larger refactor, to see high-value, low-risk native replacements.

**When not to use:** to justify swapping a working idiom for a trendier one, or to add a dependency —
this skill only proposes reducing custom code in favor of what the installed versions already provide.

## Prerequisites

- Read access to the target path and to `AGENTS.md` (§1 guardrails).
- Laravel Boost `search-docs` available to confirm each native API exists in the installed versions.

## Method

1. Detect installed versions (framework 13.x, packages) — do not assume.
2. For each candidate pattern, confirm the native API exists in these versions via Boost `search-docs`.
   Never suggest an API absent from the installed version.
3. Read the code; identify obsolete/verbose/duplicated patterns with a native equivalent.
4. **Do not modify anything yet.** Produce a ranked report.

## What to look for

- Manual validation blocks that a Form Request or a first-party rule already covers.
- Hand-rolled response shaping instead of API Resources; manual pagination instead of `paginate()`.
- `$casts` property instead of the `casts()` method; date math instead of Carbon helpers.
- Custom collection loops instead of `Arr`/`Collection`/higher-order messages.
- Manual authorization `if` chains instead of policies, gates, `@can`, or `permission:*` middleware.
- Bespoke helpers duplicating `Str`, `Arr`, `Number`, `Str::of()`, `when()`, `tap()`.
- Manual container wiring where contextual binding / method injection suffices.
- Reinventing a base convention that is the project-native equivalent: an ad-hoc envelope instead of
  `ApiResponse`; response shaping instead of `XResource`/`XSummaryResource`; hand-rolled image storage
  or accessor instead of `HasImages` (`image_url`); a `when()` filter chain instead of
  `HandlesResourceQuery::applyDashboardFilters()`; a per-section Structure class instead of a
  `Support/sections.php` registry entry; manual FCM/broadcast instead of `PushNotifier::send()`.

Apply the base's own guardrails: prefer native only when it reduces complexity, improves correctness
or readability, or removes duplication (see `AGENTS.md` §1). Within this base, **native also means its
own shared conventions** (`docs/architecture.md` → "Shared conventions"): treat `ApiResponse`, the
resource pattern, `HasImages`, `HandlesResourceQuery`, the section registry, and `PushNotifier` as the
canonical target rather than a bespoke reimplementation. **Newer is not automatically better** — do not
propose a change that only swaps a working idiom for a trendier one, and never suggest adding
Livewire/Alpine/Tailwind to the Vuexy dashboard.

## Output format

```
NN [HIGH|MEDIUM|LOW] VALUE — <path:line>
Current:      <short snippet or description>
Native (13.x): <the Laravel-native replacement + doc reference>
Reason:       <complexity/correctness/readability/duplication>
Risk:         <low|med|high>   Tests affected: <list or none>
```

Rank High→Low. Then wait: apply only the items the user names (e.g. "apply 1, 3, 5"), and run the
gates after applying.

### Sample entry

    01 [HIGH] VALUE — app/Modules/Auth/Http/Services/Api/V1/UserProfileService.php:88
    Current:       manual foreach builds a response array, then response()->json(...)
    Native (13.x): App\Modules\Base\Http\Responses\ApiResponse::success(data: new UserResource($user))
    Reason:        removes duplication — one response contract already exists in the base
    Risk:          low     Tests affected: tests/Feature/Auth/ProfileTest.php
