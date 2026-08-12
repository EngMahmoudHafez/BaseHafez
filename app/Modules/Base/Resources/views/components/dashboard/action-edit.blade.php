@props([
    'href' => '#',
    'icon' => 'edit',
    'variant' => 'primary',
    'tooltip' => null,
])

<x-dashboard.icon-link
    :href="$href"
    :icon="$icon"
    :variant="$variant"
    :tooltip="$tooltip ?? __('dashboard.edit')"
    {{ $attributes }}
/>
