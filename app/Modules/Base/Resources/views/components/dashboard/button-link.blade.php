@props([
    'href' => '#',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'outline' => false,
    'disabled' => false,
    'block' => false,
    'target' => '_self',
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
    if ($disabled) {
        $classes .= ' disabled';
    }
@endphp

<a
    href="{{ $disabled ? 'javascript:void(0)' : $href }}"
    {{ $attributes->merge(['class' => trim($classes)]) }}
    @if ($disabled) aria-disabled="true" tabindex="-1" @endif
    @if ($target !== '_self') target="{{ $target }}" @endif
>
    @if ($iconClass && $iconPosition === 'left')
        <i class="{{ $iconClass }}"></i>
    @endif

    <span>{{ $slot }}</span>

    @if ($iconClass && $iconPosition === 'right')
        <i class="{{ $iconClass }}"></i>
    @endif
</a>
