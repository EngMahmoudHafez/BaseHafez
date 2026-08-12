@extends('base::components.dashboard.layouts.auth')

@section('title', __('auth.forgot_password'))
@section('auth_form_kicker', app()->getLocale() === 'ar' ? 'خطوة واحدة فقط' : 'One quick step')
@section('auth_form_title', __('auth.forgot_password'))
@section('auth_form_subtitle', __('auth.forgot_password_instruction'))

@section('auth_form')
    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">{{ session('status') }}</div>
    @endif

    <form action="{{ route('auth.password.email') }}" method="POST">
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
                    placeholder="{{ __('auth.email') }}"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    required
                />
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mb-3 w-100">{{ __('auth.send_reset_link') }}</button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center gap-1">
                <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                {{ __('auth.back_to_login') }}
            </a>
        </div>
    </form>
@endsection
