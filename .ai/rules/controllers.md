---
paths:
  - app/Modules/**/Http/Controllers/**
---

# Controllers

Reference: `app/Modules/Auth/Http/Controllers/Dashboard/User/UserController.php`.

- Controllers translate HTTP only. No queries, no business logic, no transactions — delegate the
  whole workflow to a single injected Service and return what it produces.
- Constructor-inject one Service as a `private readonly` promoted property.
- Authorize with middleware: `implements HasMiddleware` and a `middleware()` method mapping
  `permission:<name>` (Laratrust) to actions. Do not check roles inline with `if`.
- Validate with a Form Request type-hint on the action, never `$request->validate()` in the body.
- Return a precise type: `View`, `RedirectResponse`, `JsonResponse`, `StreamedResponse`, or an API
  Resource. Dashboard controllers return views/redirects; API controllers return Resources.
- Extend `App\Http\Controllers\Controller`. Keep actions to the resourceful set plus intentful verbs
  already used in the module (e.g. `toggle`, `export`).
- Do not build responses for the API by hand — use `Http/Resources/V1` resources.
