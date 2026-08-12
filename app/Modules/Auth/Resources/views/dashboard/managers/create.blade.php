@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.create_manager'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.create_manager')"
            :breadcrumbs="[
                ['name' => __('dashboard.staff'), 'route' => 'managers.index'],
                ['name' => __('dashboard.create_manager')],
            ]"
        />

        <x-dashboard.form-page
            :action="route('managers.store')"
            method="POST"
            enctype="multipart/form-data"
            :cancel="route('managers.index')"
            :submit-label="__('dashboard.create')"
        >
            @include('auth::dashboard.managers.partials.form')
        </x-dashboard.form-page>
    </div>
@endsection
