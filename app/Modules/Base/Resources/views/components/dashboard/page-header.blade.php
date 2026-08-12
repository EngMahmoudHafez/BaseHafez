@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<header {{ $attributes->class(['dashboard-page-header']) }}>
    <div>
        @if ($breadcrumbs !== [])
            <nav aria-label="{{ __('dashboard.breadcrumb') }}">
                <ol class="breadcrumb dashboard-breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">{{ __('dashboard.dashboard') }}</a>
                    </li>
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-item">
                            @if (isset($breadcrumb['route']))
                                <a href="{{ route($breadcrumb['route'], $breadcrumb['params'] ?? []) }}">{{ $breadcrumb['name'] }}</a>
                            @else
                                {{ $breadcrumb['name'] }}
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <h1 class="dashboard-page-title">{{ $title }}</h1>
        @if ($description)
            <p class="dashboard-page-description">{{ $description }}</p>
        @endif
    </div>

    @php
        // The named `actions` slot is preferred; the default slot is the
        // fallback so pages passing header buttons without <x-slot:actions>
        // (e.g. roles/list) still render them.
        $headerActions = $actions ?? $slot;
    @endphp
    @if (trim($headerActions) !== '')
        <div class="dashboard-page-actions">{{ $headerActions }}</div>
    @endif
</header>
