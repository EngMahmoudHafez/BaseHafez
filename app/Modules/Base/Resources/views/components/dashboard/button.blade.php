@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'outline' => false,
    'disabled' => false,
    'block' => false,
    'loading' => false,
])

@php
    $classes = 'btn d-inline-flex align-items-center justify-content-center gap-2';

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
    $sizeKey = is_string($size) ? $size : 'md';
    $sizeClass = $sizeMap[$sizeKey] ?? '';
    if ($sizeClass) {
        $classes .= ' ' . $sizeClass;
    }

    // Icon
    $iconClass = $icon ? dashboard_icon_class($icon) : null;

    // Block
    if ($block) {
        $classes .= ' w-100';
    }

    // Disabled
    if ($disabled || $loading) {
        $classes .= ' disabled';
    }
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => trim($classes)]) }}
    @if ($disabled || $loading) disabled @endif
>
    @if ($loading)
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    @endif

    @if ($iconClass && $iconPosition === 'left' && ! $loading)
        <i class="{{ $iconClass }}"></i>
    @endif

    <span>{{ $slot }}</span>

    @if ($iconClass && $iconPosition === 'right' && ! $loading)
        <i class="{{ $iconClass }}"></i>
    @endif
</button>
