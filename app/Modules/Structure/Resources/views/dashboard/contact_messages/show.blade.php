@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.View Message'))

@section('content')
    @php
        $manager = auth('manager')->user();
        $canUpdate = $manager?->hasPermission('contact-messages-update');
        $canDelete = $manager?->hasPermission('contact-messages-delete');
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.View Message')"
            :breadcrumbs="[
                ['name' => __('dashboard.Contact Messages'), 'route' => 'dashboard.contact-messages.index'],
                ['name' => __('dashboard.View Message')],
            ]"
        >
            <x-slot:actions>
                @if ($canUpdate)
                    <form method="POST" action="{{ route('dashboard.contact-messages.toggle-read', $message) }}">
                        @csrf
                        @method('PATCH')
                        <x-dashboard.button
                            type="submit"
                            :variant="$message->is_read ? 'secondary' : 'success'"
                            :icon="$message->is_read ? 'mail' : 'mail-check'"
                        >
                            {{ $message->is_read ? __('dashboard.mark_as_unread') : __('dashboard.mark_as_read') }}
                        </x-dashboard.button>
                    </form>
                @endif
                @if ($canDelete)
                    <x-dashboard.delete-button
                        :delete-route="route('dashboard.contact-messages.destroy', $message)"
                        :item-name="$message->name"
                    />
                @endif
            </x-slot:actions>
        </x-dashboard.page-header>

        <x-dashboard.details>
            <x-dashboard.detail :label="__('dashboard.name')" :value="$message->name" />
            <x-dashboard.detail :label="__('dashboard.email')">
                <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
            </x-dashboard.detail>
            <x-dashboard.detail :label="__('dashboard.phone')">
                <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a>
            </x-dashboard.detail>
            <x-dashboard.detail :label="__('dashboard.status')">
                <span class="badge bg-label-{{ $message->is_read ? 'success' : 'warning' }}">
                    {{ $message->is_read ? __('dashboard.read') : __('dashboard.unread') }}
                </span>
            </x-dashboard.detail>
            <x-dashboard.detail :label="__('dashboard.date')" :value="$message->created_at->format('Y-m-d H:i:s')" />
            <x-dashboard.detail :label="__('dashboard.message')">
                <div style="white-space: pre-wrap">{{ $message->message }}</div>
            </x-dashboard.detail>
        </x-dashboard.details>
    </div>
@endsection
