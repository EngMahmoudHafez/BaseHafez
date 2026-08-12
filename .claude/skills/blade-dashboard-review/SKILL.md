---
name: blade-dashboard-review
description: Review dashboard Blade screens against this base's Vuexy/Bootstrap 5 design system. Use when adding or changing views under Resources/views/dashboard — checks components, escaping, authorization, states, RTL/LTR, accessibility, and JS. Read-only: reports findings, does not rewrite.
---

# Blade Dashboard Review

Reviews dashboard Blade against `docs/dashboard-design-system.md` and `.ai/rules/blade.md`. The stack
is Bootstrap 5 + Tabler icons (Vuexy) with shared `x-dashboard.*` components — no other UI framework.

## When to use

- After editing any `**/Resources/views/dashboard/**` screen or a shared `x-dashboard.*` component.

**When not to use:** API-only or non-dashboard Blade, or copy/translation-only edits with no markup change.

## Prerequisites

- `docs/dashboard-design-system.md` and `.ai/rules/blade.md` are readable.
- The screen renders, or you have the Blade plus the data its service passes it.

## Checklist

**Structure & components**
- Screen `@extends('base::components.dashboard.layouts.master')` and opens with `<x-dashboard.page-header>`
  (title, breadcrumbs, primary action). Enforced by `FoundationArchitectureTest`.
- Complete screen lives in its owning module, not in `Base`.
- Hand-rolled markup instead of the shared component vocabulary: **list** = `<x-dashboard.filter-bar>`
  (with `<x-dashboard.filter-select>`) + `<x-dashboard.table>` / `<x-dashboard.table-empty>` + row
  `<x-dashboard.actions>` (`action-view` / `action-edit` / `action-toggle` / `delete-button`);
  **create/edit** = `<x-dashboard.form-page>` + `<x-dashboard.field>`; **show** = `<x-dashboard.details>`
  + `<x-dashboard.detail>`. Never re-implement a table or form by hand.
- List screen whose service hand-writes filtering instead of declaring `searchable`/`filterable` and
  calling `HandlesResourceQuery::applyDashboardFilters()`; reference screens are Auth users/managers/roles
  and Notifications.
- Large Blade file mixing many responsibilities; extract partials/components.

**Safety & correctness**
- Escaped `{{ }}` for db/user/flash/editor values; `{!! !!}` only for app-generated allow-listed HTML.
- Server → JS via `@js`, never raw Blade interpolation inside `<script>`.
- No DB queries or business logic in the view; data arrives ready from the service.
- Authorization rendered with `@can`/`@cannot`, not `auth()->user()->role` comparisons.

**States & UX**
- Empty state uses `.dashboard-empty-state` (never an empty `<tbody>`); loading and error states present.
- Tables: row actions at the logical end with accessible labels/tooltips; consistent status badges;
  pagination on long lists.
- Forms: every control has a visible translated label + stable `id`; validation shown beside the field;
  primary submit last; destructive actions use the shared confirmation component.

**i18n, responsive, a11y**
- All copy via `lang/` keys (ar + en). Verify RTL + LTR.
- Usable at mobile width without fixed-pixel layouts. Keyboard focus order sane; icons have labels.
- Icons via `dashboard_icon_class()` / shared button components — no new icon set.

**Assets/JS**
- Page behavior in `@section('page-script')`; shared JS in `resources/js`. New Vite input only when a
  current view references it (`vite.config.js` stays explicit; no globs).
- No unnecessary JavaScript where a Bootstrap/Blade component already does the job.

## Output

Findings grouped High→Low with `path:line`, the issue, and the design-system-aligned fix. Note when a
change needs an Arabic/English translation key or a new shared component.

### Sample finding

> **Medium** · `app/Modules/Auth/Resources/views/dashboard/users/index.blade.php:31` · renders a raw
> `<table>` with an empty `<tbody>` on no results. · Replace with `<x-dashboard.table :paginator>` +
> `<x-dashboard.table-empty>`, and back the list with `applyDashboardFilters()`. · needs: no new key.
