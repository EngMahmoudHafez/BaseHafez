@extends('base::components.dashboard.layouts.blankLayout')

@section('title', trim($__env->yieldContent('code') . ' — ' . $__env->yieldContent('heading')))

@section('content')
    <div
        class="container-xxl container-p-y d-flex flex-column justify-content-center align-items-center text-center"
        style="min-height: 80vh"
    >
        <h1 class="fw-bold mb-2 text-primary" style="font-size: clamp(4rem, 15vw, 8rem); line-height: 1">
            @yield('code')
        </h1>
        <h4 class="mb-2">@yield('heading')</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width: 32rem">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-primary">{{ __('errors.back_home') }}</a>
    </div>
@endsection
