@php
    use App\Modules\Base\Support\DashboardMenuActiveResolver;
    use Illuminate\Support\Facades\Route;

    $manager = auth('manager')->user();
    $menuActive = app(DashboardMenuActiveResolver::class);
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

    $isVisible = static function ($item) use ($checkPermission, $hasRoute): bool {
        if (isset($item->permission) && ! $checkPermission($item->permission)) {
            return false;
        }
        if (isset($item->route) && ! $hasRoute($item->route)) {
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

<ul class="menu-sub">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            @if (! $isVisible($submenu))
                @continue
            @endif

            {{-- active menu method --}}
            @php
                $activeClass = $menuActive->itemClass($submenu, ($configData['layout'] ?? 'vertical') === 'vertical');
            @endphp

            <li class="menu-item {{ $activeClass }}">
                <a
                    href="{{ $menuHref($submenu) }}"
                    class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                    @if (isset($submenu->target) and ! empty($submenu->target)) target="_blank" @endif
                >
                    @if (isset($submenu->icon))
                        <i class="{{ $submenu->icon }}"></i>
                    @endif
                    <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
                    @isset($submenu->badge)
                        <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">
                            {{ $submenu->badge[1] }}
                        </div>
                    @endisset
                </a>

                {{-- submenu --}}
                @if (isset($submenu->submenu))
                    @include('base::components.dashboard.layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
                @endif
            </li>
        @endforeach
    @endif
</ul>
