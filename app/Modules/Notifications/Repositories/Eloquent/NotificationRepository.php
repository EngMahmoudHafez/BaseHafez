<?php

namespace App\Modules\Notifications\Repositories\Eloquent;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private readonly Notification $model) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Notification
    {
        return $this->model->newQuery()->create($attributes);
    }

    /** @return LengthAwarePaginator<int, Notification> */
    public function paginateForUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * The latest unread notifications, bounded so a user who never reads them
     * cannot make this endpoint materialize an unbounded set. Use the paginated
     * index or countUnreadForUser() for the exact total.
     *
     * @return Collection<int, Notification>
     */
    public function unreadForUser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->unread()
            ->latest()
            ->limit(100)
            ->get();
    }

    public function findForUserOrFail(int $userId, int $notificationId): Notification
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->findOrFail($notificationId);
    }

    public function countUnreadForUser(int $userId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->unread()
            ->count();
    }

    public function markAllAsReadForUser(int $userId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function deleteReadForUser(int $userId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('is_read', true)
            ->delete();
    }

    public function deleteOlderThan(int $days): int
    {
        return $this->model->newQuery()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
