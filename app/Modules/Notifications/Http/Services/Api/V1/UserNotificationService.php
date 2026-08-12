<?php

namespace App\Modules\Notifications\Http\Services\Api\V1;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserNotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    public function paginate(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($userId, $perPage);
    }

    public function unread(int $userId): Collection
    {
        return $this->notifications->unreadForUser($userId);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notifications->countUnreadForUser($userId);
    }

    public function findForUser(int $userId, int $notificationId): Notification
    {
        return $this->notifications->findForUserOrFail($userId, $notificationId);
    }

    public function markAsRead(int $userId, int $notificationId): Notification
    {
        return $this->findForUser($userId, $notificationId)->markAsRead();
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->notifications->markAllAsReadForUser($userId);
    }

    public function deleteRead(int $userId): int
    {
        return $this->notifications->deleteReadForUser($userId);
    }

    public function delete(int $userId, int $notificationId): void
    {
        $this->findForUser($userId, $notificationId)->delete();
    }
}
