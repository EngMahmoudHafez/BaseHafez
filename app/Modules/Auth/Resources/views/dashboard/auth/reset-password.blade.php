@extends('base::components.dashboard.layouts.auth')

@section('title', __('auth.reset_password'))
@section('auth_form_kicker', app()->getLocale() === 'ar' ? 'أمان وتجربة أفضل' : 'Security with polish')
@section('auth_form_title', __('auth.reset_password'))
@section('auth_form_subtitle', __('auth.reset_password_instruction'))

@section('auth_form')
    <form action="{{ route('auth.password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="{{ dashboard_icon_class('mail') }}"></i></span>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="{{ __('auth.email') }}"
                    value="{{ old('email', $email) }}"
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
            <label for="password" class="form-label">{{ __('auth.new_password') }}</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="{{ dashboard_icon_class('lock') }}"></i></span>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="{{ __('auth.new_password') }}"
                    autocomplete="new-password"
                    required
                />
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('auth.password_confirmation') }}</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="{{ dashboard_icon_class('lock') }}"></i></span>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="{{ __('auth.password_confirmation') }}"
                    autocomplete="new-password"
                    required
                />
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-3 w-100">{{ __('auth.reset_password_button') }}</button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center gap-1">
                <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                {{ __('auth.back_to_login') }}
            </a>
        </div>
    </form>
@endsection
