@extends('base::components.dashboard.layouts.blankLayout')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
    @yield('styles')
@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover min-vh-100">
        <div class="authentication-inner row min-vh-100 m-0">
            <aside class="d-none d-lg-flex col-lg-7 align-items-center justify-content-center bg-label-primary p-5">
                <div class="mw-100" style="max-width: 34rem">
                    <p class="text-primary fw-semibold mb-3">{{ config('app.name') }}</p>
                    <h1 class="display-6 fw-bold mb-3">{{ __('dashboard.auth_welcome_title') }}</h1>
                    <p class="fs-5 text-body-secondary mb-0">{{ __('dashboard.auth_welcome_description') }}</p>
                </div>
            </aside>

            <main class="d-flex col-lg-5 align-items-center bg-body p-sm-5 col-12 p-4">
                <div class="w-px-400 mx-auto">
                    <div class="d-lg-none mb-5">
                        <span class="fs-4 fw-bold text-primary">{{ config('app.name') }}</span>
                    </div>

                    <div class="mb-4">
                        @hasSection('auth_form_kicker')
                            <span class="badge bg-label-primary mb-2">@yield('auth_form_kicker')</span>
                        @endif
                        <h2 class="fw-bold mb-2">@yield('auth_form_title')</h2>
                        <p class="text-body-secondary">@yield('auth_form_subtitle')</p>
                    </div>

                    @yield('auth_form')

                    <p class="text-body-secondary small mt-5 mb-0 text-center">
                        © {{ now()->year }} {{ config('app.name') }} — {{ __('dashboard.all_rights_reserved') }}
                    </p>
                </div>
            </main>
        </div>
    </div>
@endsection

@section('page-script')
    @yield('scripts')
@endsection
