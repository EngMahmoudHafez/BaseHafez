@extends('base::components.dashboard.layouts.master')

@section('title', $user->name)

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="$user->name"
            :breadcrumbs="[['name' => __('dashboard.users'), 'route' => 'users.index']]"
        >
            <x-slot:actions>
                <x-dashboard.button-link :href="route('users.edit', $user->id)" variant="primary" icon="edit">
                    {{ __('dashboard.edit') }}
                </x-dashboard.button-link>
            </x-slot:actions>
        </x-dashboard.page-header>

        <x-dashboard.details>
            <x-dashboard.detail :label="__('dashboard.Email')" :value="$user->email" />
            <x-dashboard.detail :label="__('dashboard.Phone')" :value="$user->phone" />
            <x-dashboard.detail :label="__('dashboard.whatsapp')" :value="$user->whatsapp" />
            <x-dashboard.detail :label="__('dashboard.country')" :value="$user->country?->name" />
            <x-dashboard.detail :label="__('dashboard.Status')">
                <span class="badge bg-label-{{ $user->status->value === 'active' ? 'success' : 'secondary' }}">{{ __('dashboard.' . $user->status->value) }}</span>
            </x-dashboard.detail>
            <x-dashboard.detail :label="__('dashboard.Created At')" :value="$user->created_at?->format('Y-m-d H:i')" />
        </x-dashboard.details>
    </div>
@endsection
