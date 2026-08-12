@php
    use App\Modules\Base\Support\DashboardMenuActiveResolver;
    use Illuminate\Support\Facades\Route;

    $configData = Helper::appClasses();
    $menuActive = app(DashboardMenuActiveResolver::class);
    $manager = auth('manager')->user();

    $hasRoute = static fn (?string $name): bool => $name ? Route::has($name) : false;

    $checkPermission = static function ($permission) use ($manager): bool {
        if (! $permission) {
            return true;
        }
        if (! $manager) {
            return false;
        }
        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($manager->hasPermission($p)) {
                    return true;
                }
            }

            return false;
        }

        return $manager->hasPermission($permission);
    };

    $isVisible = null;
    $isVisible = static function ($menu) use (&$isVisible, $checkPermission, $hasRoute): bool {
        if (isset($menu->menuHeader)) {
            return ! isset($menu->permission) || $checkPermission($menu->permission);
        }

        if (isset($menu->permission) && ! $checkPermission($menu->permission)) {
            return false;
        }

        if (isset($menu->route) && ! $hasRoute($menu->route)) {
            return false;
        }

        if (isset($menu->submenu)) {
            foreach ($menu->submenu as $sub) {
                if ($isVisible($sub)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    };

    $menuHref = static function (object $item): string {
        if (isset($item->url)) {
            return $item->url;
        }

        if (! isset($item->route)) {
            return 'javascript:void(0);';
        }

        return route($item->route);
    };
@endphp

<aside
    id="layout-menu"
    class="layout-menu menu-vertical menu"
    @foreach ($configData['menuAttributes'] as $attribute => $value)
        {{ $attribute }}="{{ $value }}"
    @endforeach
>
    <!-- ! Hide app brand if navbar-full -->
    @if (! isset($navbarFull))
        <div class="app-brand demo">
            <a href="{{ route('dashboard.home') }}" class="app-brand-link">
                <span class="app-brand-logo demo">@include('base::components.dashboard.layouts._partials.macros')</span>
                <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
                <i class="icon-base ti ti-x d-block d-xl-none"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            @if (! $isVisible($menu))
                @continue
            @endif

            {{-- menu headers --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small">
                    <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
                </li>
            @else
                {{-- active menu method --}}
                @php
                    $activeClass = $menuActive->itemClass($menu, true);
                @endphp

                {{-- main menu --}}
                <li class="menu-item {{ $activeClass }}">
                    <a
                        href="{{ $menuHref($menu) }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and
                                ! empty($menu->target)) target="_blank" @endif
                    >
                        @isset($menu->icon)
                            <i class="{{ $menu->icon }}"></i>
                        @endisset
                        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>

                    {{-- submenu --}}
                    @isset($menu->submenu)
                        @include('base::components.dashboard.layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>
</aside>
