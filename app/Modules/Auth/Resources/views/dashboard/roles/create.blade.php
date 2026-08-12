@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.Add Role'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.Add Role')"
            :breadcrumbs="[
                ['name' => __('dashboard.Roles List'), 'route' => 'roles.index'],
                ['name' => __('dashboard.Add Role')],
            ]"
        />

        <x-dashboard.form-page
            :action="route('roles.store')"
            method="POST"
            :cancel="route('roles.index')"
            :submit-label="__('dashboard.Add Role')"
        >
            @include('auth::dashboard.roles.partials.form', ['selectedPermissions' => []])
        </x-dashboard.form-page>
    </div>
@endsection
