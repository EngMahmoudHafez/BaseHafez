@php
    $configData = Helper::appClasses();
    $isFront = true;
@endphp

@extends('base::components.dashboard.layouts.commonMaster')

@section('layoutContent')
    @include('base::components.dashboard.layouts.sections.navbar.navbar-front')

    <!-- Sections:Start -->
    @yield('content')
    <!-- / Sections:End -->

    @include('base::components.dashboard.layouts.sections.footer.footer-front')
@endsection
