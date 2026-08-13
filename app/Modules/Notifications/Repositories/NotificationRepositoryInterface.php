<?php

namespace App\Modules\Notifications\Repositories;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface NotificationRepositoryInterface
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Notification;

    /** @return LengthAwarePaginator<int, Notification> */
    public function paginateFor(Model $notifiable, int $perPage): LengthAwarePaginator;

    /** @return Collection<int, Notification> */
    public function unreadFor(Model $notifiable): Collection;

    public function findForOrFail(Model $notifiable, int $notificationId): Notification;

    public function countUnreadFor(Model $notifiable): int;

    public function markAllAsReadFor(Model $notifiable): int;

    public function deleteReadFor(Model $notifiable): int;

    public function deleteOlderThan(int $days): int;
}
