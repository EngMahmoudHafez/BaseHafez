<?php

namespace App\Modules\Auth\database\seeders;

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('foundation.admin.email');
        $password = config('foundation.admin.password');

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command?->warn('Skipping manager creation: BASE_ADMIN_EMAIL is not configured.');

            return;
        }

        if (! is_string($password) || mb_strlen($password) < 12) {
            $this->command?->warn('Skipping manager creation: BASE_ADMIN_PASSWORD must contain at least 12 characters.');

            return;
        }

        $manager = Manager::query()->firstOrCreate(
            ['email' => strtolower($email)],
            [
                'name' => config('foundation.admin.name', 'Super Admin'),
                'phone' => config('foundation.admin.phone'),
                'password' => $password,
                'status' => UserStatus::Active,
            ],
        );

        $superAdminRole = Role::query()
            ->where('name', RoleName::SuperAdmin->value)
            ->first();

        if ($superAdminRole && ! $manager->hasRole($superAdminRole)) {
            $manager->addRole($superAdminRole);
        }
    }
}
