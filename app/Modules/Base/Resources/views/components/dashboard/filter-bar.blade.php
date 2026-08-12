@props([
    'action' => null,
    'searchName' => 'search',
    'searchPlaceholder' => null,
])

@php
    $action = $action ?? request()->url();
    $searchPlaceholder = $searchPlaceholder ?? __('dashboard.search');
@endphp

{{-- GET filter form. A search input (bound to request($searchName)), an
     optional `filters` slot of x-dashboard.filter-select controls, a submit
     button and a reset link back to the bare action (all query cleared). --}}
<div {{ $attributes->class(['card dashboard-card mb-4']) }}>
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET" action="{{ $action }}">
            <div class="col-lg-4 col-12">
                <label class="form-label" for="{{ $searchName }}">{{ __('dashboard.search') }}</label>
                <input
                    class="form-control"
                    id="{{ $searchName }}"
                    name="{{ $searchName }}"
                    type="search"
                    value="{{ request($searchName) }}"
                    placeholder="{{ $searchPlaceholder }}"
                />
            </div>

            {{ $filters ?? '' }}

            <div class="col-lg-auto d-flex col-12 gap-2">
                <x-dashboard.button type="submit" variant="primary" icon="filter">
                    {{ __('dashboard.filter') }}
                </x-dashboard.button>
                <x-dashboard.button-link :href="$action" variant="secondary" icon="x">
                    {{ __('dashboard.reset') }}
                </x-dashboard.button-link>
            </div>
        </form>
    </div>
</div>
