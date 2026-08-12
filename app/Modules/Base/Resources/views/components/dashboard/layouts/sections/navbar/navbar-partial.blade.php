@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

    $manager = auth('manager')->user();
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex ms-0 me-4 py-0">
        <a href="{{ route('dashboard.home') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('base::components.dashboard.layouts._partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') ?: config('app.name') }}</span>
        </a>

        <!-- Display menu close icon only for horizontal-menu with navbar-full -->
        @if (isset($menuHorizontal))
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-xl-none ms-auto">
                <i class="icon-base ti ti-x icon-sm d-flex align-items-center justify-content-center"></i>
            </a>
        @endif
    </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (! isset($navbarHideToggle))
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link me-xl-6 px-0" href="javascript:void(0)">
            <i class="icon-base ti ti-menu-2 icon-md"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    @if ($configData['hasCustomizer'] == true)
        <!-- Style Switcher -->
        <div class="navbar-nav align-items-center">
            <li class="nav-item dropdown me-xl-0 me-2">
                <a
                    class="nav-link dropdown-toggle hide-arrow"
                    id="nav-theme"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                >
                    <i class="icon-base ti ti-sun icon-md theme-icon-active"></i>
                    <span class="d-none ms-2" id="nav-theme-text">{{ __('dashboard.theme_toggle') }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="nav-theme-text">
                    <li>
                        <button
                            type="button"
                            class="dropdown-item align-items-center active"
                            data-bs-theme-value="light"
                            aria-pressed="false"
                        >
                            <span
                                ><i class="icon-base ti ti-sun icon-22px me-3" data-icon="sun"></i
                                >{{ __('dashboard.theme_light') }}</span>
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item align-items-center"
                            data-bs-theme-value="dark"
                            aria-pressed="true"
                        >
                            <span
                                ><i class="icon-base ti ti-moon-stars icon-22px me-3" data-icon="moon-stars"></i
                                >{{ __('dashboard.theme_dark') }}</span>
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item align-items-center"
                            data-bs-theme-value="system"
                            aria-pressed="false"
                        >
                            <span
                                ><i
                                    class="icon-base ti ti-device-desktop-analytics icon-22px me-3"
                                    data-icon="device-desktop-analytics"
                                ></i
                                >{{ __('dashboard.theme_system') }}</span>
                        </button>
                    </li>
                </ul>
            </li>
        </div>
        <!-- / Style Switcher-->
    @endif

    <ul class="navbar-nav align-items-center ms-auto flex-row">
        <!-- Language Toggle -->
        <li class="nav-item dropdown me-3">
            <a
                class="nav-link dropdown-toggle btn btn-sm btn-outline-secondary hide-arrow px-3 py-1"
                href="javascript:void(0);"
                data-bs-toggle="dropdown"
            >
                <i class="icon-base ti ti-language icon-md me-1"></i>
                {{ LaravelLocalization::getCurrentLocale() === 'ar' ? __('dashboard.language_arabic') : __('dashboard.language_english') }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a
                        class="dropdown-item {{ LaravelLocalization::getCurrentLocale() === 'ar' ? 'active' : '' }}"
                        href="{{ LaravelLocalization::getLocalizedURL('ar', request()->fullUrl()) }}"
                    >
                        {{ __('dashboard.language_arabic') }}
                    </a>
                </li>
                <li>
                    <a
                        class="dropdown-item {{ LaravelLocalization::getCurrentLocale() === 'en' ? 'active' : '' }}"
                        href="{{ LaravelLocalization::getLocalizedURL('en', request()->fullUrl()) }}"
                    >
                        {{ __('dashboard.language_english') }}
                    </a>
                </li>
            </ul>
        </li>

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <x-dashboard.avatar class="avatar-online" :name="$manager?->name" :image="$manager?->image_url" />
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <div class="dropdown-item mt-0">
                        <div class="d-flex align-items-center">
                            <div class="me-2 flex-shrink-0">
                                <x-dashboard.avatar class="avatar-online" :name="$manager?->name" :image="$manager?->image_url" />
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $manager->name ?? 'Admin' }}</h6>
                                <small class="text-body-secondary">{{ $manager->email ?? '' }}</small>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="dropdown-divider mx-n2 my-1"></div>
                </li>
                @if ($manager && Route::has('settings.edit'))
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.edit') }}">
                            <i class="icon-base ti ti-user icon-md me-3"></i
                            ><span class="align-middle">{{ __('dashboard.Edit Profile') }}</span>
                        </a>
                    </li>
                @endif
                <li>
                    <div class="dropdown-divider mx-n2 my-1"></div>
                </li>
                <li>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="icon-base ti ti-power icon-md me-3"></i
                            ><span class="align-middle">{{ __('dashboard.Logout') }}</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>
