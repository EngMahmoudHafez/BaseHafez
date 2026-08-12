<?php

namespace App\Modules\Structure\Providers;

use App\Modules\Base\Providers\ModuleServiceProvider;

/**
 * Boots the Structure module. Routes, migrations, views and translations are
 * loaded by the base ModuleServiceProvider; the module no longer registers any
 * bespoke Blade view components — the generic section editor uses the shared
 * `x-dashboard.*` primitives.
 */
final class StructureServiceProvider extends ModuleServiceProvider {}
