<?php

namespace Tests\Fixtures;

use App\Modules\Auth\Models\User;
use App\Modules\Base\Concerns\HasDeviceTokens;

/**
 * A User that opts into device-token registration. Used to exercise the
 * HasDeviceTokens trait and PushNotifier without modifying the shipped User model.
 */
class NotifiableUser extends User
{
    use HasDeviceTokens;

    protected $table = 'users';
}
