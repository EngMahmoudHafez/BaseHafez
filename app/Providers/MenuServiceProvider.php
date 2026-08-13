<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('base::components.dashboard.layouts.*', function ($view): void {
            $view->with('menuData', [
                $this->menu('verticalMenu.json'),
                $this->menu('horizontalMenu.json'),
            ]);
        });
    }

    private function menu(string $fileName): object
    {
        $menuJson = file_get_contents(resource_path("menu/{$fileName}"));

        if ($menuJson === false) {
            throw new \RuntimeException("Unable to read menu file: {$fileName}");
        }

        return json_decode($menuJson, flags: JSON_THROW_ON_ERROR);
    }
}
