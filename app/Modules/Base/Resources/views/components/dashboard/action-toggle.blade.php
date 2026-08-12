@props([
    'route' => '#',
    'active' => false,
    'tooltip' => null,
    'activeIcon' => 'ban',
    'inactiveIcon' => 'circle-check',
])

@php
    $isActive = (bool) $active;
    $icon = $isActive ? $activeIcon : $inactiveIcon;
    $variant = $isActive ? 'warning' : 'success';
@endphp

{{-- Status toggle: POST form spoofing PATCH. `:active` is the current state;
     when active the button offers to deactivate (warning) and vice versa. --}}
<form class="d-inline-flex" method="POST" action="{{ $route }}">
    @csrf
    @method('PATCH')
    <x-dashboard.icon-button
        type="submit"
        :icon="$icon"
        :variant="$variant"
        :tooltip="$tooltip ?? __('dashboard.change_status')"
        {{ $attributes }}
    />
</form>
