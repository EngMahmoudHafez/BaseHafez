<footer class="landing-footer border-top bg-body py-4">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between container gap-2">
        <span class="text-body-secondary small">
            © {{ now()->year }} {{ config('app.name') }}. {{ __('dashboard.all_rights_reserved') }}.
        </span>

        @if (config('variables.creatorUrl'))
            <a class="small" href="{{ config('variables.creatorUrl') }}" target="_blank" rel="noopener noreferrer">
                {{ config('variables.creatorName') }}
            </a>
        @endif
    </div>
</footer>
