<?php

namespace App\Modules\Notifications\Providers;

use App\Modules\Base\Providers\ModuleServiceProvider;

class NotificationServiceProvider extends ModuleServiceProvider
{
    protected function moduleViewNamespace(): string
    {
        return 'notifications';
    }
}
