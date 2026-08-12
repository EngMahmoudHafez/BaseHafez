<?php

namespace App\Modules\Auth\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Pending = 'pending';
}
