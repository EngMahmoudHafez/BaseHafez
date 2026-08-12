@php
    use Illuminate\Support\Facades\Route;

    $currentRouteName = Route::currentRouteName();
    $activeRoutes = ['front-pages-pricing', 'front-pages-payment', 'front-pages-checkout', 'front-pages-help-center'];
    $activeClass = in_array($currentRouteName, $activeRoutes) ? 'active' : '';
@endphp
<!-- Navbar: Start -->
<nav class="layout-navbar py-0 shadow-none">
    <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-md-8 px-3">
            <!-- Menu logo wrapper: Start -->
            <div class="navbar-brand app-brand demo d-flex me-xl-8 ms-0 me-4 py-0">
                <!-- Mobile menu toggle: Start-->
                <button
                    class="navbar-toggler me-4 border-0 px-0"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="{{ __('dashboard.toggle_navigation') }}"
                >
                    <i class="icon-base ti ti-menu-2 icon-lg text-heading fw-medium align-middle"></i>
                </button>
                <!-- Mobile menu toggle: End-->
                <a href="javascript:;" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        @include('base::components.dashboard.layouts._partials.macros')
                    </span>
                    <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">{{ config('variables.templateName') ?: config('app.name') }}</span>
                </a>
            </div>
            <!-- Menu logo wrapper: End -->
            <!-- Menu wrapper: Start -->
            <div class="navbar-collapse landing-nav-menu collapse" id="navbarSupportedContent">
                <button
                    class="navbar-toggler text-heading position-absolute scaleX-n1-rtl end-0 top-0 border-0 p-2"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="{{ __('dashboard.toggle_navigation') }}"
                >
                    <i class="icon-base ti ti-x icon-lg"></i>
                </button>
            </div>
            <div class="landing-menu-overlay d-lg-none"></div>
            <!-- Menu wrapper: End -->
            <!-- Toolbar: Start -->
            <ul class="navbar-nav align-items-center ms-auto flex-row">
                @if ($configData['hasCustomizer'] == true)
                    <!-- Style Switcher -->
                    <li class="nav-item dropdown-style-switcher dropdown me-xl-1 me-2">
                        <a
                            class="nav-link dropdown-toggle hide-arrow"
                            id="nav-theme"
                            href="javascript:void(0);"
                            data-bs-toggle="dropdown"
                        >
                            <i class="icon-base ti ti-sun icon-lg theme-icon-active"></i>
                            <span class="d-none ms-2" id="nav-theme-text">{{ __('dashboard.theme_toggle') }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item align-items-center active"
                                    data-bs-theme-value="light"
                                    aria-pressed="false"
                                >
                                    <span
                                        ><i class="icon-base ti ti-sun icon-md me-3" data-icon="sun"></i
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
                                        ><i class="icon-base ti ti-moon-stars icon-md me-3" data-icon="moon-stars"></i
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
                                            class="icon-base ti ti-device-desktop-analytics icon-md me-3"
                                            data-icon="device-desktop-analytics"
                                        ></i
                                        >{{ __('dashboard.theme_system') }}</span>
                                </button>
                            </li>
                        </ul>
                    </li>
                    <!-- / Style Switcher-->
                @endif

                <!-- navbar button: Start -->
                <li>
                    <a href="{{ route('login') }}" class="btn btn-primary"
                        ><span class="icon-base ti ti-login scaleX-n1-rtl me-md-1"></span
                        ><span class="d-none d-md-block">{{ __('dashboard.login_or_register') }}</span></a>
                </li>
                <!-- navbar button: End -->
            </ul>
            <!-- Toolbar: End -->
        </div>
    </div>
</nav>
<!-- Navbar: End -->
