---
paths:
  - app/Modules/**/Resources/views/**
  - resources/views/**
---

# Blade & Dashboard

The dashboard is Bootstrap 5 + Tabler icons (Vuexy). Full contract: `docs/dashboard-design-system.md`.

- **No second UI stack.** Do not add Livewire, Alpine, Tailwind-in-Blade, Vue, React, or another
  icon/CSS framework to dashboard screens. Use Bootstrap 5 utilities and the shared tokens in
  `resources/css/app.css`.
- Every dashboard screen `@extends('base::components.dashboard.layouts.master')` and opens with
  `<x-dashboard.page-header>` (enforced by `FoundationArchitectureTest`). Complete screens live in
  their owning module; `Base` holds only layout and `x-dashboard.*` primitives.
- Prefer a Blade component over duplicated markup. Use `x-dashboard.*`, form, and table components
  before writing raw HTML. Reuse via props and `$attributes`.
- **Blade compiles to PHP — keep directives simple.** Use the `@php … @endphp` block, never the inline
  `@php(...)` form (Blade's block regex spans it to the next `@endphp` and swallows the directives in
  between). Keep an `@include` / `@includeWhen` argument to a variable or short expression; compute a
  nested ternary or `match` in a `@php` block first. Blade is **not** auto-formatted — the Pint
  prettier-blade fixer was removed for silently corrupting such directives — so hand-format it.
  `FoundationArchitectureTest` compiles every view and rejects invalid PHP or the inline `@php(...)` form.
- **Escape by default:** `{{ }}` for any database/user/flash/editor value. Use `{!! !!}` only for
  application-generated HTML already passed through an allow-list. Pass server values to JS with
  `@js` — never interpolate raw Blade into a `<script>` literal.
- No queries or business logic in views. Pass ready data from the service. Render authorization with
  `@can` / `@cannot`, not `auth()->user()->role` checks.
- Icons come from `dashboard_icon_class()` / the shared button components. Never render an empty
  table body — use `.dashboard-empty-state`. Page-specific JS goes in `@section('page-script')`.
- Verify Arabic + English, RTL + LTR, mobile width, and empty/loading/error states when changing a page.
