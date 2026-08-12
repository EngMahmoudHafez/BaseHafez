@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.Roles List'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.Roles List')"
            :breadcrumbs="[['name' => __('dashboard.Roles List')]]"
        >
            @if (auth('manager')->user()?->hasPermission('roles-create'))
                <x-slot:actions>
                    <x-dashboard.button-link :href="route('roles.create')" variant="primary" icon="plus">
                        {{ __('dashboard.Add Role') }}
                    </x-dashboard.button-link>
                </x-slot:actions>
            @endif
        </x-dashboard.page-header>

        <x-dashboard.filter-bar :action="route('roles.index')" />

        <x-dashboard.table :paginator="$roles">
            <x-slot:head>
                <th scope="col">#</th>
                <th scope="col">{{ __('dashboard.Name') }}</th>
                <th scope="col">{{ __('dashboard.Description') }}</th>
                <th class="text-end" scope="col">{{ __('dashboard.Actions') }}</th>
            </x-slot:head>

            @forelse ($roles as $role)
                <tr>
                    <td>{{ $roles->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="fw-medium">{{ app()->isLocale('ar') ? $role->display_name_ar : $role->display_name_en }}</span>
                        <small class="d-block text-body-secondary">{{ $role->name }}</small>
                    </td>
                    <td>{{ $role->description ?: '—' }}</td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            @if (auth('manager')->user()?->hasPermission('roles-read'))
                                <x-dashboard.action-view :href="route('roles.show', $role->id)" />
                            @endif
                            @if (auth('manager')->user()?->hasPermission('roles-update'))
                                <x-dashboard.action-edit :href="route('roles.edit', $role->id)" />
                            @endif
                            @if (auth('manager')->user()?->hasPermission('roles-delete'))
                                <x-dashboard.delete-button
                                    :item-name="app()->isLocale('ar') ? $role->display_name_ar : $role->display_name_en"
                                    :delete-route="route('roles.destroy', $role->id)"
                                />
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="4" :message="__('dashboard.No data available')" />
            @endforelse
        </x-dashboard.table>
    </div>
@endsection
