@extends('base::components.dashboard.layouts.master')

@section('title', __('notifications.dashboard.title'))

@section('content')
    @php
        $readOptions = [
            '0' => __('notifications.status.unread'),
            '1' => __('notifications.status.read'),
        ];
        $actor = auth('manager')->user();
        $canCreate = (bool) $actor?->hasPermission('notifications-create');
        $canDelete = (bool) $actor?->hasPermission('notifications-delete');
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('notifications.dashboard.title')"
            :description="__('notifications.dashboard.description')"
            :breadcrumbs="[['name' => __('notifications.dashboard.title')]]"
        >
            <x-slot:actions>
                @if ($canCreate)
                    <x-dashboard.button
                        type="button"
                        variant="primary"
                        icon="send"
                        data-bs-toggle="modal"
                        data-bs-target="#broadcastModal"
                    >
                        {{ __('notifications.actions.broadcast') }}
                    </x-dashboard.button>
                @endif
                @if ($canDelete && $statistics['total'] > 0)
                    <form
                        action="{{ route('dashboard.notifications.delete-all') }}"
                        method="POST"
                        onsubmit="return confirm(@js(__('notifications.messages.confirm_delete_all')))"
                    >
                        @csrf
                        @method('DELETE')
                        <x-dashboard.button type="submit" variant="danger" outline icon="trash">
                            {{ __('notifications.actions.delete_all') }}
                        </x-dashboard.button>
                    </form>
                @endif
            </x-slot:actions>
        </x-dashboard.page-header>

        <div class="dashboard-stats-grid mb-4">
            @foreach ([
                ['label' => __('notifications.dashboard.total_notifications'), 'value' => $statistics['total'], 'icon' => 'bell', 'color' => 'primary'],
                ['label' => __('notifications.dashboard.sent_today'), 'value' => $statistics['sent_today'], 'icon' => 'send', 'color' => 'success'],
                ['label' => __('notifications.dashboard.unread_notifications'), 'value' => $statistics['unread'], 'icon' => 'mail', 'color' => 'warning'],
            ] as $stat)
                <article class="dashboard-stat-card h-100">
                    <span class="dashboard-stat-card__icon bg-label-{{ $stat['color'] }}">
                        <i class="{{ dashboard_icon_class($stat['icon']) }}"></i>
                    </span>
                    <div>
                        <span class="text-muted">{{ $stat['label'] }}</span>
                        <h2 class="mb-0">{{ number_format($stat['value']) }}</h2>
                    </div>
                </article>
            @endforeach
        </div>

        <x-dashboard.filter-bar :action="route('dashboard.notifications.index')">
            <x-slot:filters>
                <x-dashboard.filter-select name="type" :label="__('notifications.fields.type')" :options="$types" />
                <x-dashboard.filter-select
                    name="is_read"
                    :label="__('notifications.fields.is_read')"
                    :options="$readOptions"
                />
                <div class="col-lg-3 col-12">
                    <label class="form-label" for="from_date">{{ __('dashboard.from_date') }}</label>
                    <input
                        id="from_date"
                        type="date"
                        name="from_date"
                        class="form-control"
                        value="{{ request('from_date') }}"
                    />
                </div>
            </x-slot:filters>
        </x-dashboard.filter-bar>

        <x-dashboard.table :paginator="$notifications">
            <x-slot:head>
                <th>{{ __('notifications.fields.user') }}</th>
                <th>{{ __('notifications.fields.title') }}</th>
                <th>{{ __('notifications.fields.type') }}</th>
                <th>{{ __('notifications.fields.is_read') }}</th>
                <th>{{ __('notifications.fields.sent_at') }}</th>
                <th class="text-end">{{ __('dashboard.actions') }}</th>
            </x-slot:head>

            @forelse ($notifications as $notification)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $notification->notifiable->name }}</div>
                        <small class="text-muted">{{ $notification->notifiable->phone }}</small>
                    </td>
                    <td>
                        <div class="fw-medium">{{ \Illuminate\Support\Str::limit($notification->title, 45) }}</div>
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($notification->body, 65) }}</small>
                    </td>
                    <td><span class="badge bg-label-primary">{{ $types[$notification->type] }}</span></td>
                    <td>
                        <span class="badge bg-label-{{ $notification->is_read ? 'success' : 'warning' }}">
                            {{ $notification->is_read ? __('notifications.status.read') : __('notifications.status.unread') }}
                        </span>
                    </td>
                    <td>{{ optional($notification->sent_at)->format('Y-m-d H:i') }}</td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            <x-dashboard.action-view :href="route('dashboard.notifications.show', $notification)" />
                            @if ($canDelete)
                                <x-dashboard.delete-button
                                    :delete-route="route('dashboard.notifications.destroy', $notification)"
                                    :item-name="$notification->title"
                                />
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="6">
                    <i class="{{ dashboard_icon_class('bell-off') }}"></i>
                    <p class="mb-0">{{ __('notifications.messages.no_notifications') }}</p>
                </x-dashboard.table-empty>
            @endforelse
        </x-dashboard.table>
    </div>

    @include('notifications::dashboard.partials.broadcast-modal')
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const targets = document.querySelectorAll('input[name="target_type"]');
            const users = document.getElementById('notification-users-field');
            const syncTarget = () => {
                const selected = document.querySelector('input[name="target_type"]:checked');
                users.classList.toggle('d-none', selected?.value !== 'users');
            };

            targets.forEach((target) => target.addEventListener('change', syncTarget));
            syncTarget();
        });
    </script>
@endsection
