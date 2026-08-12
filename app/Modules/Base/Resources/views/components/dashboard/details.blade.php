{{-- Canonical read-only show layout: a responsive detail grid of
     x-dashboard.detail cells inside a dashboard card. --}}
<div {{ $attributes->class(['card dashboard-card']) }}>
    <div class="card-body">
        <dl class="dashboard-details-grid">{{ $slot }}</dl>
    </div>
</div>
