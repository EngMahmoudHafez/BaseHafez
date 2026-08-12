@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.Edit Role'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.Edit Role')"
            :description="$role->name"
            :breadcrumbs="[
                ['name' => __('dashboard.Roles List'), 'route' => 'roles.index'],
                ['name' => __('dashboard.Edit Role')],
            ]"
        />

        <x-dashboard.form-page
            :action="route('roles.update', $role->id)"
            method="PUT"
            :cancel="route('roles.index')"
            :submit-label="__('dashboard.update')"
        >
            @include('auth::dashboard.roles.partials.form')
        </x-dashboard.form-page>
    </div>
@endsection
