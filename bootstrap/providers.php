<?php

use App\Modules\Base\Providers\BaseServiceProvider;
use App\Modules\Base\Providers\RepositoryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\MenuServiceProvider;
use App\Support\ModuleDiscovery;
use Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider;

$providers = [
    // Must load before module route files call LaravelLocalization::setLocale() for route prefixes
    LaravelLocalizationServiceProvider::class,
    BaseServiceProvider::class,
    RepositoryServiceProvider::class,
    MenuServiceProvider::class,
    ...ModuleDiscovery::serviceProviders(),
    AppServiceProvider::class,
];

return array_values(array_unique($providers));
