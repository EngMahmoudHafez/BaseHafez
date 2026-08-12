@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.edit_password'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.edit_password')"
            :description="$manager->name"
            :breadcrumbs="[
                ['name' => __('dashboard.staff'), 'route' => 'managers.index'],
                ['name' => __('dashboard.edit_password')],
            ]"
        />

        <x-dashboard.form-page
            :action="route('managers.password.update', $manager)"
            method="PUT"
            :cancel="route('managers.index')"
            :submit-label="__('dashboard.update')"
        >
            <div class="row g-3">
                <x-dashboard.field
                    name="password"
                    type="password"
                    :label="__('dashboard.password')"
                    minlength="8"
                    required
                />

                <x-dashboard.field
                    name="password_confirmation"
                    type="password"
                    :label="__('dashboard.password_confirmation')"
                    minlength="8"
                    required
                />
            </div>
        </x-dashboard.form-page>
    </div>
@endsection
