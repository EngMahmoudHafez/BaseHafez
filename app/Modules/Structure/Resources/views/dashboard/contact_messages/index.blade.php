@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.Contact Messages'))

@section('content')
    @php
        $manager = auth('manager')->user();
        $canUpdate = $manager?->hasPermission('contact-messages-update');
        $canDelete = $manager?->hasPermission('contact-messages-delete');
        $readOptions = ['0' => __('dashboard.unread'), '1' => __('dashboard.read')];
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.Contact Messages')"
            :breadcrumbs="[['name' => __('dashboard.Contact Messages')]]"
        />

        <x-dashboard.filter-bar :action="route('dashboard.contact-messages.index')">
            <x-slot:filters>
                <x-dashboard.filter-select name="is_read" :label="__('dashboard.status')" :options="$readOptions" />
            </x-slot:filters>
        </x-dashboard.filter-bar>

        <x-dashboard.table :paginator="$messages">
            <x-slot:head>
                <th>#</th>
                <th>{{ __('dashboard.name') }}</th>
                <th>{{ __('dashboard.email') }}</th>
                <th>{{ __('dashboard.message') }}</th>
                <th>{{ __('dashboard.status') }}</th>
                <th>{{ __('dashboard.date') }}</th>
                <th class="text-end">{{ __('dashboard.actions') }}</th>
            </x-slot:head>

            @forelse ($messages as $message)
                <tr @class(['table-active' => ! $message->is_read])>
                    <td>{{ $message->id }}</td>
                    <td>
                        <div class="fw-medium">{{ $message->name }}</div>
                        <small class="text-muted">{{ $message->phone }}</small>
                    </td>
                    <td>{{ $message->email }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($message->message, 60) }}</td>
                    <td>
                        <span class="badge bg-label-{{ $message->is_read ? 'success' : 'warning' }}">
                            {{ $message->is_read ? __('dashboard.read') : __('dashboard.unread') }}
                        </span>
                    </td>
                    <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            <x-dashboard.action-view :href="route('dashboard.contact-messages.show', $message)" />
                            @if ($canUpdate)
                                <x-dashboard.action-toggle
                                    :route="route('dashboard.contact-messages.toggle-read', $message)"
                                    :active="$message->is_read"
                                    active-icon="mail"
                                    inactive-icon="mail-check"
                                    :tooltip="$message->is_read ? __('dashboard.mark_as_unread') : __('dashboard.mark_as_read')"
                                />
                            @endif
                            @if ($canDelete)
                                <x-dashboard.delete-button
                                    :delete-route="route('dashboard.contact-messages.destroy', $message)"
                                    :item-name="$message->name"
                                />
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="7" :message="__('dashboard.no_data')" />
            @endforelse
        </x-dashboard.table>
    </div>
@endsection
