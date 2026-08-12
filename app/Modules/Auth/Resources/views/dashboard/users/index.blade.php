@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.users'))

@section('content')
    @php
        $statusOptions = collect(\App\Modules\Auth\Enums\UserStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->value => __('dashboard.' . $status->value)])
            ->all();
        $actor = auth('manager')->user();
        $canCreate = (bool) $actor?->hasPermission('users-create');
        $canUpdate = (bool) $actor?->hasPermission('users-update');
        $canDelete = (bool) $actor?->hasPermission('users-delete');
        $canExport = (bool) $actor?->hasPermission('users-export');
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header :title="__('dashboard.users')">
            <x-slot:actions>
                @if ($canExport)
                    <x-dashboard.button-link
                        :href="route('users.export', request()->query())"
                        variant="secondary"
                        icon="download"
                    >
                        {{ __('dashboard.export') }}
                    </x-dashboard.button-link>
                @endif
                @if ($canCreate)
                    <x-dashboard.button-link :href="route('users.create')" variant="primary" icon="plus">
                        {{ __('dashboard.create_user') }}
                    </x-dashboard.button-link>
                @endif
            </x-slot:actions>
        </x-dashboard.page-header>

        <x-dashboard.filter-bar :action="route('users.index')">
            <x-slot:filters>
                <x-dashboard.filter-select name="status" :label="__('dashboard.Status')" :options="$statusOptions" />
            </x-slot:filters>
        </x-dashboard.filter-bar>

        <x-dashboard.table :paginator="$users">
            <x-slot:head>
                <th>{{ __('dashboard.Name') }}</th>
                <th>{{ __('dashboard.contact') }}</th>
                <th>{{ __('dashboard.Status') }}</th>
                <th class="text-end">{{ __('dashboard.actions') }}</th>
            </x-slot:head>

            @forelse ($users as $user)
                <tr>
                    <td><a href="{{ route('users.show', $user->id) }}">{{ $user->name }}</a></td>
                    <td>{{ $user->email ?: $user->whatsapp }}</td>
                    <td>
                        <span class="badge bg-label-{{ $user->status->value === 'active' ? 'success' : 'secondary' }}">{{ __('dashboard.' . $user->status->value) }}</span>
                    </td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            <x-dashboard.action-view :href="route('users.show', $user->id)" />
                            @if ($canUpdate)
                                <x-dashboard.action-edit :href="route('users.edit', $user->id)" />
                                <x-dashboard.action-toggle
                                    :route="route('users.toggle', $user->id)"
                                    :active="$user->status->value === 'active'"
                                />
                            @endif
                            @if ($canDelete)
                                <x-dashboard.delete-button
                                    :delete-route="route('users.destroy', $user->id)"
                                    :item-name="$user->name"
                                />
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="4" :message="__('dashboard.no_data')" />
            @endforelse
        </x-dashboard.table>
    </div>
@endsection
