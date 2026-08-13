<?php

namespace App\Modules\Base\Providers;

use App\Modules\Base\Console\Commands\BaseDoctorCommand;
use App\Modules\Base\Console\Commands\BaseSetupCommand;
use App\Modules\Base\Console\Commands\MakeModuleCommand;
use App\Modules\Base\Console\Commands\MakeModuleModelCommand;
use App\Modules\Base\Console\Commands\ModulesListCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class BaseServiceProvider extends ServiceProvider
{
    public const AUTO_DISCOVER = false;

    public function boot(): void
    {
        $this->registerCommands();
        $this->loadViews();
        $this->registerAnonymousComponents();
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                BaseSetupCommand::class,
                BaseDoctorCommand::class,
                ModulesListCommand::class,
                MakeModuleCommand::class,
                MakeModuleModelCommand::class,
            ]);
        }
    }

    private function loadViews(): void
    {
        $views = base_path('app/Modules/Base/Resources/views');
        if (is_dir($views)) {
            $this->loadViewsFrom($views, 'base');
        }
    }

    private function registerAnonymousComponents(): void
    {
        Blade::component('base::components.dashboard.button', 'dashboard.button');
        Blade::component('base::components.dashboard.button-link', 'dashboard.button-link');
        Blade::component('base::components.dashboard.icon-button', 'dashboard.icon-button');
        Blade::component('base::components.dashboard.icon-link', 'dashboard.icon-link');
        Blade::component('base::components.dashboard.delete-button', 'dashboard.delete-button');
        Blade::component('base::components.dashboard.page-header', 'dashboard.page-header');
        Blade::component('base::components.dashboard.avatar', 'dashboard.avatar');

        Blade::component('base::components.dashboard.table', 'dashboard.table');
        Blade::component('base::components.dashboard.table-empty', 'dashboard.table-empty');
        Blade::component('base::components.dashboard.filter-bar', 'dashboard.filter-bar');
        Blade::component('base::components.dashboard.filter-select', 'dashboard.filter-select');
        Blade::component('base::components.dashboard.actions', 'dashboard.actions');
        Blade::component('base::components.dashboard.action-view', 'dashboard.action-view');
        Blade::component('base::components.dashboard.action-edit', 'dashboard.action-edit');
        Blade::component('base::components.dashboard.action-toggle', 'dashboard.action-toggle');
        Blade::component('base::components.dashboard.form-page', 'dashboard.form-page');
        Blade::component('base::components.dashboard.field', 'dashboard.field');
        Blade::component('base::components.dashboard.details', 'dashboard.details');
        Blade::component('base::components.dashboard.detail', 'dashboard.detail');
    }
}
