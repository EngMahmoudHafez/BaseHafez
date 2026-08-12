@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.create_user'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.create_user')"
            :breadcrumbs="[['name' => __('dashboard.users'), 'route' => 'users.index']]"
        />

        <x-dashboard.form-page
            :action="route('users.store')"
            method="POST"
            enctype="multipart/form-data"
            :cancel="route('users.index')"
            :submit-label="__('dashboard.create')"
        >
            @include('auth::dashboard.users.partials.form')
        </x-dashboard.form-page>
    </div>
@endsection
