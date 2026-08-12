@extends('base::components.dashboard.layouts.master')

@section('title', $role->display_name_en ?: $role->name)

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="$role->display_name_en ?: $role->name"
            :breadcrumbs="[
                ['name' => __('dashboard.roles'), 'route' => 'roles.index'],
                ['name' => $role->display_name_en ?: $role->name],
            ]"
        >
            <x-slot:actions>
                <x-dashboard.button-link :href="route('roles.edit', $role)" variant="primary" icon="edit">
                    {{ __('dashboard.edit') }}
                </x-dashboard.button-link>
            </x-slot:actions>
        </x-dashboard.page-header>

        <x-dashboard.details>
            <x-dashboard.detail :label="__('dashboard.Description')" :value="$role->description" />
            <x-dashboard.detail :label="__('dashboard.permissions')">
                <div class="d-flex flex-wrap gap-2">
                    @forelse ($role->permissions as $permission)
                        <span class="badge bg-label-primary">{{ $permission->name }}</span>
                    @empty
                        <span class="text-muted">{{ __('dashboard.no_data') }}</span>
                    @endforelse
                </div>
            </x-dashboard.detail>
        </x-dashboard.details>
    </div>
@endsection
