---
paths:
  - app/Modules/**/Http/Requests/**
---

# Form Requests

- Every write action has a dedicated Form Request under `Http/Requests/{Api/V1,Dashboard}/`.
- Put all validation in `rules()`. Normalize input (trim, cast, default) in `prepareForValidation()`,
  not in the controller or service.
- Implement `authorize()`. Return the real authorization decision (permission/policy/ownership) when
  it is field- or payload-shaped; otherwise return `true` and authorize in controller middleware —
  never leave it returning `false`.
- Use translated messages via `lang/` keys; do not hard-code English strings in `messages()`.
- Validate every incoming field. For updates, scope `unique` rules with `Rule::unique(...)->ignore($id)`.
- Reuse a shared `Rules/` class for repeated domain rules (e.g. phone, password) instead of copying
  the rule array across requests.
- Keep API and Dashboard requests separate even when similar; they evolve independently.
