@props([
    'href' => '#',
    'icon' => 'eye',
    'variant' => 'info',
    'tooltip' => null,
])

<x-dashboard.icon-link
    :href="$href"
    :icon="$icon"
    :variant="$variant"
    :tooltip="$tooltip ?? __('dashboard.show')"
    {{ $attributes }}
/>
