---
paths:
  - app/Modules/**/Http/Services/**
---

# Services

Services own a complete workflow. They live in `Http/Services/Api/V1` or `Http/Services/Dashboard`
and are injected into controllers.

- A service method owns one workflow end to end: authorize side effects, coordinate repositories/
  models, run the transaction, and return the view/redirect (dashboard) or the data/Resource (API).
- Wrap any multi-write workflow in `DB::transaction()`. When replacing a stored file, write the new
  file first and delete the old one only after the database write succeeds; clean up the new file if
  the write fails.
- Depend on repository interfaces for reusable persistence; use the model directly for a one-off read.
- Do not touch the HTTP layer beyond returning a response/view. Do not emit queries that belong in a
  repository when the same query is reused elsewhere.
- Keep services free of validation (that is the Form Request) and of authorization gates that already
  live in controller middleware; a service only enforces invariants the middleware cannot express.
- Prefer Laravel-native helpers (events, notifications, jobs, `Str`, `Arr`) over bespoke utilities.
