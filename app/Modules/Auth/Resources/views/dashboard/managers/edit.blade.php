@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.edit_manager'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.edit_manager')"
            :breadcrumbs="[
                ['name' => __('dashboard.staff'), 'route' => 'managers.index'],
                ['name' => __('dashboard.edit_manager')],
            ]"
        />

        <x-dashboard.form-page
            :action="route('managers.update', $manager)"
            method="PUT"
            enctype="multipart/form-data"
            :cancel="route('managers.index')"
            :submit-label="__('dashboard.update')"
        >
            @include('auth::dashboard.managers.partials.form')
        </x-dashboard.form-page>
    </div>
@endsection
