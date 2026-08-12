<?php

namespace App\Modules\Base\Support;

use Illuminate\Support\Facades\Route;

class DashboardMenuActiveResolver
{
    public function itemClass(object $menuItem, bool $vertical = true): ?string
    {
        if (! $this->branchIsActive($menuItem, Route::currentRouteName())) {
            return null;
        }

        if (isset($menuItem->submenu)) {
            return $vertical ? 'active open' : 'active';
        }

        return 'active';
    }

    public function branchIsActive(object $menuItem, ?string $currentRoute): bool
    {
        if ($this->itemIsActive($menuItem, $currentRoute)) {
            return true;
        }

        foreach ($menuItem->submenu ?? [] as $childItem) {
            if ($this->branchIsActive($childItem, $currentRoute)) {
                return true;
            }
        }

        return false;
    }

    public function itemIsActive(object $menuItem, ?string $currentRoute): bool
    {
        if ($currentRoute === null) {
            return false;
        }

        if (isset($menuItem->route) && $this->routeMatches($menuItem->route, $currentRoute)) {
            return true;
        }

        return isset($menuItem->slug) && $menuItem->slug === $currentRoute;
    }

    private function routeMatches(string $menuRoute, string $currentRoute): bool
    {
        if ($menuRoute === $currentRoute) {
            return true;
        }

        $routePrefix = str_ends_with($menuRoute, '.index')
            ? substr($menuRoute, 0, -strlen('.index'))
            : $menuRoute;

        return str_starts_with($currentRoute, $routePrefix . '.');
    }
}
