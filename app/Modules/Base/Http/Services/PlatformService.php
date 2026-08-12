<?php

namespace App\Modules\Base\Http\Services;

abstract class PlatformService
{
    abstract public static function platform(): string;
}
