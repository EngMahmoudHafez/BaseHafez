# Base module

Base owns shared infrastructure only: canonical module bootstrapping, repository primitives, common HTTP helpers, and reusable dashboard layout/components.

Create modules with `php artisan make:module` and add model surfaces with `php artisan make:module-model`. The authoritative paths and rules are in the root `AGENTS.md` and `docs/architecture.md`.

Do not place a complete Auth, Notifications, Structure, or future business screen in this module.
