@props([
    'icon' => 'help-circle',
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'size' => 'sm', // sm, md, lg - default sm for table actions
    'outline' => false,
    'disabled' => false,
    'tooltip' => null,
])

@php
    $classes = 'btn btn-icon table-action-btn';

    // Variant
    if ($outline) {
        $classes .= ' btn-outline-' . $variant;
    } else {
        $classes .= ' btn-' . $variant;
    }

    // Size
    $sizeMap = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
    $sizeKey = is_string($size) ? $size : 'sm';
    $sizeClass = $sizeMap[$sizeKey] ?? $sizeMap['sm'];

    $classes .= ' ' . $sizeClass;

    // Disabled
    if ($disabled) {
        $classes .= ' disabled';
    }

    $iconClass = dashboard_icon_class($icon);
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => trim($classes)]) }}
    @if ($disabled) disabled @endif
    @if ($tooltip) title="{{ $tooltip }}" data-bs-toggle="tooltip" @endif
    aria-label="{{ $tooltip ?? $icon }}"
>
    <span class="dashboard-btn-icon" aria-hidden="true">
        <i class="{{ $iconClass }}"></i>
    </span>
</button>
