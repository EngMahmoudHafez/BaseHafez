# Structure module

Structure owns editable public-site content and contact-message management.

Content is a **declarative page registry**: every content section is one generic
"page" defined in `Support/sections.php` (fields, groups, repeatable items,
defaults, and `public`/`translated` flags). One generic editor
(`Http/Controllers/Dashboard/SectionController` + `Resources/views/dashboard/section.blade.php`),
generic validation (`Support/SectionRules` via `SectionRequest`), and generic
serialization (`SectionRegistry` + `StructureService`) are driven from it.
**Adding a page is a single registry entry — no per-section controller, request,
or view.**

Pages marked `public => true` are served by a single generic endpoint:
`GET /web/v1/structures/{key}` (unknown or non-public keys return 404).

It does not own authentication, notification delivery, or product-specific CMS behavior.
