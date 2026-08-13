@extends('base::components.dashboard.layouts.master')

@section('title', __('notifications.dashboard.notification_details'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('notifications.dashboard.notification_details')"
            :breadcrumbs="[
                ['name' => __('notifications.dashboard.title'), 'route' => 'dashboard.notifications.index'],
                ['name' => __('notifications.dashboard.notification_details')],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.notifications.index') }}" class="btn btn-label-secondary">
                    <i class="{{ dashboard_icon_class('arrow-left') }} me-1"></i>{{ __('dashboard.back') }}
                </a>
            </x-slot:actions>
        </x-dashboard.page-header>

        <section class="card dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">{{ $notification->title }}</h2>
                <span class="badge bg-label-{{ $notification->is_read ? 'success' : 'warning' }}">
                    {{ $notification->is_read ? __('notifications.status.read') : __('notifications.status.unread') }}
                </span>
            </div>
            <div class="card-body">
                <dl class="dashboard-details-grid">
                    <x-dashboard.detail :label="__('notifications.fields.user')" :value="$notification->notifiable->name" />
                    <x-dashboard.detail :label="__('notifications.fields.type')" :value="__('notifications.types.' . $notification->type)" />
                    <x-dashboard.detail :label="__('notifications.fields.sent_at')" :value="optional($notification->sent_at)->format('Y-m-d H:i')" />
                    <x-dashboard.detail :label="__('notifications.fields.title_ar')" :value="$notification->title_ar" />
                    <x-dashboard.detail :label="__('notifications.fields.title_en')" :value="$notification->title_en ?: '—'" />
                </dl>

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <h3 class="h6 text-muted">{{ __('notifications.fields.body_ar') }}</h3>
                        <div class="bg-body-tertiary rounded p-3">{!! nl2br(e($notification->body_ar)) !!}</div>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h6 text-muted">{{ __('notifications.fields.body_en') }}</h3>
                        <div class="bg-body-tertiary rounded p-3">{!! nl2br(e($notification->body_en ?: '—')) !!}</div>
                    </div>
                </div>

                @if ($notification->action_url)
                    <div class="mt-4">
                        <h3 class="h6 text-muted">{{ __('notifications.fields.action_url') }}</h3>
                        <a
                            href="{{ $notification->action_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >{{ $notification->action_url }}</a>
                    </div>
                @endif
            </div>
            <div class="card-footer text-end">
                <x-dashboard.delete-button
                    :delete-route="route('dashboard.notifications.destroy', $notification)"
                    :item-name="$notification->title"
                    size="md"
                />
            </div>
        </section>
    </div>
@endsection
