<?php

namespace App\Modules\Notifications\Repositories\Eloquent;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private readonly Notification $model) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Notification
    {
        return $this->model->newQuery()->create($attributes);
    }

    /** @return LengthAwarePaginator<int, Notification> */
    public function paginateFor(Model $notifiable, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * The latest unread notifications, bounded so a recipient who never reads them
     * cannot make this endpoint materialize an unbounded set. Use the paginated
     * index or countUnreadFor() for the exact total.
     *
     * @return Collection<int, Notification>
     */
    public function unreadFor(Model $notifiable): Collection
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
            ->unread()
            ->latest()
            ->limit(100)
            ->get();
    }

    public function findForOrFail(Model $notifiable, int $notificationId): Notification
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
            ->findOrFail($notificationId);
    }

    public function countUnreadFor(Model $notifiable): int
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
            ->unread()
            ->count();
    }

    public function markAllAsReadFor(Model $notifiable): int
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function deleteReadFor(Model $notifiable): int
    {
        return $this->model->newQuery()
            ->whereMorphedTo('notifiable', $notifiable)
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
