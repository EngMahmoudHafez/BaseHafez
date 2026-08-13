<?php

namespace App\Modules\Notifications\Http\Services\Api\V1;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserNotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    /** @return LengthAwarePaginator<int, Notification> */
    public function paginate(Model $notifiable, int $perPage): LengthAwarePaginator
    {
        return $this->notifications->paginateFor($notifiable, $perPage);
    }

    /** @return Collection<int, Notification> */
    public function unread(Model $notifiable): Collection
    {
        return $this->notifications->unreadFor($notifiable);
    }

    public function unreadCount(Model $notifiable): int
    {
        return $this->notifications->countUnreadFor($notifiable);
    }

    public function findForNotifiable(Model $notifiable, int $notificationId): Notification
    {
        return $this->notifications->findForOrFail($notifiable, $notificationId);
    }

    public function markAsRead(Model $notifiable, int $notificationId): Notification
    {
        return $this->findForNotifiable($notifiable, $notificationId)->markAsRead();
    }

    public function markAllAsRead(Model $notifiable): int
    {
        return $this->notifications->markAllAsReadFor($notifiable);
    }

    public function deleteRead(Model $notifiable): int
    {
        return $this->notifications->deleteReadFor($notifiable);
    }

    public function delete(Model $notifiable, int $notificationId): void
    {
        $this->findForNotifiable($notifiable, $notificationId)->delete();
    }
}
