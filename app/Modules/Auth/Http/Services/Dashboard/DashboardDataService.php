<?php

namespace App\Modules\Auth\Http\Services\Dashboard;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Collection;

class DashboardDataService
{
    /** @return array<string, mixed> */
    public function statistics(Manager $manager): array
    {
        return [
            'manager' => $manager,
            'statCards' => $this->statCards(),
            'recentUsers' => $this->recentUsers(),
        ];
    }

    /** @return list<array{label: string, value: int, icon: string, theme: string}> */
    private function statCards(): array
    {
        return [
            $this->statCard('dashboard.total_users', User::query()->count(), 'users', 'primary'),
            $this->statCard('dashboard.active_users', $this->activeUserCount(), 'user-check', 'success'),
            $this->statCard('dashboard.total_staff', Manager::query()->count(), 'briefcase', 'info'),
            $this->statCard('dashboard.active_staff', $this->activeManagerCount(), 'shield-check', 'warning'),
        ];
    }

    /** @return array{label: string, value: int, icon: string, theme: string} */
    private function statCard(string $label, int $value, string $icon, string $theme): array
    {
        return compact('label', 'value', 'icon', 'theme');
    }

    private function activeUserCount(): int
    {
        return User::query()->where('status', UserStatus::Active->value)->count();
    }

    private function activeManagerCount(): int
    {
        return Manager::query()->where('status', UserStatus::Active->value)->count();
    }

    /** @return Collection<int, User> */
    private function recentUsers(): Collection
    {
        return User::query()->latest()->limit(5)->get();
    }
}
