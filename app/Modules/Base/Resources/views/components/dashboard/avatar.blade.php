@props([
    'name' => '',
    'image' => null,
])

{{-- Circular avatar: shows the image when present, and falls back to the first
     letters of the first two words of the name (initials) when there is no image
     or the image fails to load. --}}
@php
    $words = array_values(array_filter(preg_split('/\s+/u', trim((string) $name)) ?: []));

    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_substr($word, 0, 1);
    }
    $initials = preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtoupper($initials));
    $initials = $initials !== '' ? $initials : '؟';

    $hasImage = filled($image);
@endphp

<div {{ $attributes->class(['avatar']) }}>
    <span
        class="avatar-initial rounded-circle bg-label-primary"
        data-avatar-initial
        @if ($hasImage) style="display: none" @endif
    >{{ $initials }}</span>

    @if ($hasImage)
        <img
            src="{{ $image }}"
            alt="{{ $name }}"
            class="rounded-circle"
            onerror="this.style.display='none';this.previousElementSibling.style.removeProperty('display')"
        />
    @endif
</div>
