@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.staff'))

@section('content')
    @php
        $statusOptions = collect($statuses)
            ->mapWithKeys(fn ($status) => [$status->value => __('dashboard.' . $status->value)])
            ->all();
        $roleOptions = $roles
            ->mapWithKeys(fn ($role) => [$role->name => $role->display_name_en ?: $role->name])
            ->all();
        $actor = auth('manager')->user();
        $canCreate = (bool) $actor?->hasPermission('managers-create');
        $canUpdate = (bool) $actor?->hasPermission('managers-update');
        $canDelete = (bool) $actor?->hasPermission('managers-delete');
        $canExport = (bool) $actor?->hasPermission('managers-export');
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.staff')"
            :description="__('dashboard.staff_description')"
            :breadcrumbs="[['name' => __('dashboard.staff')]]"
        >
            <x-slot:actions>
                @if ($canExport)
                    <x-dashboard.button-link
                        :href="route('managers.export', request()->query())"
                        variant="secondary"
                        icon="download"
                    >
                        {{ __('dashboard.export') }}
                    </x-dashboard.button-link>
                @endif
                @if ($canCreate)
                    <x-dashboard.button-link :href="route('managers.create')" variant="primary" icon="plus">
                        {{ __('dashboard.create_manager') }}
                    </x-dashboard.button-link>
                @endif
            </x-slot:actions>
        </x-dashboard.page-header>

        <x-dashboard.filter-bar :action="route('managers.index')">
            <x-slot:filters>
                <x-dashboard.filter-select name="status" :label="__('dashboard.status')" :options="$statusOptions" />
                <x-dashboard.filter-select name="role" :label="__('dashboard.role')" :options="$roleOptions" />
            </x-slot:filters>
        </x-dashboard.filter-bar>

        <x-dashboard.table :paginator="$managers">
            <x-slot:head>
                <th>{{ __('dashboard.name') }}</th>
                <th>{{ __('dashboard.contact') }}</th>
                <th>{{ __('dashboard.role') }}</th>
                <th>{{ __('dashboard.status') }}</th>
                <th class="text-end">{{ __('dashboard.actions') }}</th>
            </x-slot:head>

            @forelse ($managers as $manager)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $manager->name }}</div>
                    </td>
                    <td>
                        <div>{{ $manager->email }}</div>
                        <small class="text-muted">{{ $manager->phone }}</small>
                    </td>
                    <td>{{ $manager->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td>
                        <span class="badge bg-label-{{ $manager->isActiveAccount() ? 'success' : 'secondary' }}">{{ __("dashboard.{$manager->status->value}") }}</span>
                    </td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            <x-dashboard.action-view :href="route('managers.show', $manager)" />
                            @if ($canUpdate)
                                <x-dashboard.action-edit :href="route('managers.edit', $manager)" />
                                <x-dashboard.icon-link
                                    :href="route('managers.password.edit', $manager)"
                                    icon="key"
                                    variant="secondary"
                                    :tooltip="__('dashboard.password')"
                                />
                            @endif
                            @if ((int) auth('manager')->id() !== $manager->id)
                                @if ($canUpdate)
                                    <x-dashboard.action-toggle
                                        :route="route('managers.toggle', $manager)"
                                        :active="$manager->isActiveAccount()"
                                    />
                                @endif
                                @if ($canDelete)
                                    <x-dashboard.delete-button
                                        :delete-route="route('managers.destroy', $manager)"
                                        :item-name="$manager->name"
                                    />
                                @endif
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="5" :message="__('dashboard.no_data')" />
            @endforelse
        </x-dashboard.table>
    </div>
@endsection
