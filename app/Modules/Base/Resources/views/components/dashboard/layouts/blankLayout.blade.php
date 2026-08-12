@isset($pageConfigs)
    @php
        Helper::updatePageConfig($pageConfigs);
    @endphp
@endisset

@php
    $configData = Helper::appClasses();

    /* Display elements */
    $customizerHidden = $customizerHidden ?? '';
@endphp

@extends('base::components.dashboard.layouts.commonMaster')

@section('layoutContent')
    <!-- Content -->
    @yield('content')
    <!--/ Content -->
@endsection
