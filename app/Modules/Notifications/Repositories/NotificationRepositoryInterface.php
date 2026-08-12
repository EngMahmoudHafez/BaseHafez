<?php

namespace App\Modules\Notifications\Repositories;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function create(array $attributes): Notification;

    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator;

    public function unreadForUser(int $userId): Collection;

    public function findForUserOrFail(int $userId, int $notificationId): Notification;

    public function countUnreadForUser(int $userId): int;

    public function markAllAsReadForUser(int $userId): int;

    public function deleteReadForUser(int $userId): int;

    public function deleteOlderThan(int $days): int;
}
