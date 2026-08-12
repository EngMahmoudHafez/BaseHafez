@extends('base::components.dashboard.layouts.master')

@section('title', $manager->name)

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="$manager->name"
            :breadcrumbs="[
                ['name' => __('dashboard.staff'), 'route' => 'managers.index'],
                ['name' => $manager->name],
            ]"
        >
            <x-slot:actions>
                <x-dashboard.button-link :href="route('managers.edit', $manager)" variant="primary" icon="edit">
                    {{ __('dashboard.edit') }}
                </x-dashboard.button-link>
            </x-slot:actions>
        </x-dashboard.page-header>

        <div class="card dashboard-card mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                    @if ($manager->avatar)
                        <img
                            class="rounded border"
                            src="{{ Storage::disk('public')->url($manager->avatar) }}"
                            alt="{{ $manager->name }}"
                            width="112"
                            height="112"
                        />
                    @else
                        <div class="avatar avatar-xl bg-label-primary">
                            <span class="avatar-initial rounded">{{ mb_substr($manager->name, 0, 2) }}</span>
                        </div>
                    @endif
                    <div>
                        <h2 class="h4 mb-1">{{ $manager->name }}</h2>
                        <span class="badge bg-label-{{ $manager->isActiveAccount() ? 'success' : 'secondary' }}">{{ __("dashboard.{$manager->status->value}") }}</span>
                    </div>
                </div>
            </div>
        </div>

        <x-dashboard.details>
            <x-dashboard.detail :label="__('dashboard.email')" :value="$manager->email" />
            <x-dashboard.detail :label="__('dashboard.phone')" :value="$manager->phone" />
            <x-dashboard.detail :label="__('dashboard.whatsapp')" :value="$manager->whatsapp" />
            <x-dashboard.detail :label="__('dashboard.country')" :value="$manager->country?->name" />
            <x-dashboard.detail :label="__('dashboard.role')" :value="$manager->roles->pluck('name')->implode(', ')" />
            <x-dashboard.detail
                :label="__('dashboard.last_login_at')"
                :value="$manager->last_login_at?->format('Y-m-d H:i')"
            />
            <x-dashboard.detail
                :label="__('dashboard.created_at')"
                :value="$manager->created_at?->format('Y-m-d H:i')"
            />
        </x-dashboard.details>
    </div>
@endsection
