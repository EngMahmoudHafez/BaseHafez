@props([
    'action',
    'method' => 'POST',
    'enctype' => null,
    'cancel' => null,
    'submitLabel' => null,
])

@php
    $method = strtoupper($method);
    $formMethod = in_array($method, ['GET', 'POST'], true) ? $method : 'POST';
    $spoofMethod = in_array($method, ['PUT', 'PATCH', 'DELETE'], true) ? $method : null;
    $submitLabel = $submitLabel ?? __('dashboard.save');
@endphp

{{-- Create/edit card scaffold. Put x-dashboard.field controls (usually inside a
     `.row g-3`) in the default slot; override the footer buttons with a `footer` slot. --}}
<form
    {{ $attributes->class(['card dashboard-card']) }}
    action="{{ $action }}"
    method="{{ $formMethod }}"
    @if ($enctype) enctype="{{ $enctype }}" @endif
>
    @csrf
    @if ($spoofMethod)
        @method($spoofMethod)
    @endif

    <div class="card-body">{{ $slot }}</div>

    <div class="card-footer d-flex justify-content-end gap-2">
        @isset($footer)
            {{ $footer }}
        @else
            @if ($cancel)
                <x-dashboard.button-link :href="$cancel" variant="secondary">
                    {{ __('dashboard.cancel') }}
                </x-dashboard.button-link>
            @endif
            <x-dashboard.button type="submit" variant="primary">{{ $submitLabel }}</x-dashboard.button>
        @endisset
    </div>
</form>
