@extends('base::components.dashboard.layouts.auth')

@section('title', __('dashboard.login'))
@section('auth_form_kicker', app()->getLocale() === 'ar' ? 'دخول آمن وسريع' : 'Secure sign in')
@section('auth_form_title', __('auth.login'))
@section('auth_form_subtitle', __('auth.welcome') . '.')

@section('auth_form')
    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-3" role="alert">{{ session('error') }}</div>
    @endif

    <form action="{{ route('auth.login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="{{ dashboard_icon_class('mail') }}"></i></span>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('auth.email') }}"
                    autocomplete="email"
                    autofocus
                    required
                />
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">{{ __('auth.password') }}</label>
                <a href="{{ route('auth.password.request') }}">
                    <small>{{ __('auth.forgot_password') }}</small>
                </a>
            </div>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="{{ dashboard_icon_class('lock') }}"></i></span>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="{{ __('auth.password') }}"
                    autocomplete="current-password"
                    required
                />
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">{{ __('auth.login') }}</button>
    </form>
@endsection
