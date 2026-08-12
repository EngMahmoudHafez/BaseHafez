---
name: blade-dashboard-review
description: Review dashboard Blade screens against your project's design system and component library. Use when adding or changing dashboard views — checks components, escaping, authorization, states, i18n/RTL, accessibility, and JS. Read-only: it reports findings, it does not rewrite.
---

# Blade Dashboard Review

Reviews dashboard Blade against your design system and shared component library. It assumes one chosen
UI stack and one shared component set — no second framework bolted on.

> **Adapt before first use.** Replace every `<…>` placeholder with your stack (`<Bootstrap | Tailwind | …>`),
> your component namespace (`<e.g. x-dashboard.*>`), your design-system doc, and your list-filtering
> helper. See the bundle README.

## When to use

- After editing any `<dashboard views path>` screen or a shared `<component namespace>` component.

**When not to use:** non-dashboard Blade, or copy/translation-only edits with no markup change.

## Prerequisites

- Your design-system doc and Blade rules are readable.
- The screen renders, or you have the Blade plus the data its service passes it.

## Checklist

**Structure & components**
- Screen extends your dashboard layout and opens with your page-header component.
- Complete screens live in their owning module/area, not in the shared layout package.
- Hand-rolled markup instead of the shared vocabulary: reuse the **list** (filter bar + table + empty
  state + row actions), **form** (form wrapper + field), and **detail** (details grid) components.
  Never re-implement a table or a form by hand.
- List screens backed by your shared filtering helper (declare searchable/filterable fields), not
  hand-written query chains. Point reviewers at your `<reference screens>`.
- Large Blade file mixing many responsibilities; extract partials/components.

**Safety & correctness**
- Escaped `{{ }}` for db/user/flash/editor values; `{!! !!}` only for app-generated, allow-listed HTML.
- Server → JS via `@js`, never raw Blade interpolation inside `<script>`.
- No DB queries or business logic in the view; data arrives ready from the service.
- Authorization rendered with `@can`/`@cannot`, not inline role comparisons.

**States & UX**
- Empty state uses the shared empty component (never an empty `<tbody>`); loading and error states present.
- Row actions at the logical end with accessible labels/tooltips; consistent status badges; pagination
  on long lists.
- Every control has a visible translated label + a stable `id`; validation shown beside the field;
  primary submit last; destructive actions use a shared confirmation.

**i18n, responsive, a11y**
- All copy via translation keys, for every supported locale. Verify RTL + LTR if you support both.
- Usable at mobile width without fixed-pixel layouts; sane keyboard focus order; icons have labels.
- Icons via your shared icon helper/components — no new icon set.

**Assets / JS**
- Page behavior in your page-script section; shared JS in your JS source. Add a bundler input only when
  a current view references it.
- No unnecessary JavaScript where a component already does the job.

## Output

Findings grouped High→Low with `path:line`, the issue, and the design-system-aligned fix. Note when a
change needs a new translation key (per locale) or a new shared component.

### Sample finding

> **Medium** · `.../dashboard/users/index.blade.php:31` · renders a raw `<table>` with an empty
> `<tbody>` on no results. · Replace with your table + empty-state components, and back the list with
> your filter helper. · needs: no new translation key.
