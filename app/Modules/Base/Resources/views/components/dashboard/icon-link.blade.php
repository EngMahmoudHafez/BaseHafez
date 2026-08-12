@props([
    'icon' => 'help-circle',
    'href' => '#',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'size' => 'sm', // sm, md, lg - default sm for table actions
    'outline' => false,
    'disabled' => false,
    'tooltip' => null,
    'target' => '_self',
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

<a
    href="{{ $disabled ? '#' : $href }}"
    {{ $attributes->merge(['class' => trim($classes)]) }}
    @if ($disabled) aria-disabled="true" tabindex="-1" @endif
    @if ($tooltip) title="{{ $tooltip }}" data-bs-toggle="tooltip" @endif
    aria-label="{{ $tooltip ?? $icon }}"
    @if ($target !== '_self') target="{{ $target }}" @endif
>
    <span class="dashboard-btn-icon" aria-hidden="true">
        <i class="{{ $iconClass }}"></i>
    </span>
</a>
