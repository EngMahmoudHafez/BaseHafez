<?php

namespace App\Modules\Notifications\Http\Services\Dashboard;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\User;
use App\Modules\Base\Concerns\HandlesResourceQuery;
use App\Modules\Notifications\DTOs\NotificationMessageData;
use App\Modules\Notifications\Http\Requests\Dashboard\NotificationIndexRequest;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use App\Modules\Notifications\Services\PushNotifier;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    use HandlesResourceQuery;

    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly PushNotifier $pushNotifier,
    ) {}

    public function index(NotificationIndexRequest $request): View
    {
        $query = Notification::query()->with('notifiable');

        $this->applyDashboardFilters(
            $query,
            $request,
            searchable: ['title_ar', 'title_en', 'body_ar', 'body_en'],
            filterable: [
                'type' => 'type',
                'is_read' => 'is_read',
                'from_date' => fn (Builder $query, mixed $value) => $query->whereDate('created_at', '>=', $value),
                'to_date' => fn (Builder $query, mixed $value) => $query->whereDate('created_at', '<=', $value),
            ],
        );

        $perPage = (int) ($request->validated('per_page') ?? 15);

        return view('notifications::dashboard.index', [
            'notifications' => $query->latest()->paginate($perPage)->withQueryString(),
            'types' => Notification::getTypes(),
            'users' => User::query()
                ->where('status', UserStatus::Active->value)
                ->orderBy('name')
                ->get(['id', 'name', 'phone']),
            'statistics' => [
                'total' => Notification::query()->count(),
                'sent_today' => Notification::query()->whereDate('sent_at', today())->count(),
                'unread' => Notification::query()->unread()->count(),
            ],
        ]);
    }

    public function sendToUser(int $userId, NotificationMessageData $message): Notification
    {
        // Dashboard broadcasts target application users (validated to exist).
        $user = User::query()->findOrFail($userId);

        $notification = $this->notifications->create($message->forNotifiable($user));

        // Actually deliver it: push to the recipient's devices (queued FCM) and
        // broadcast on their private channel — not just a database row.
        $this->pushNotifier->deliverExisting($user, $notification);

        return $notification;
    }

    /**
     * @param  array<int, int|string>  $userIds
     * @return Collection<int, Notification>
     */
    public function sendToUsers(array $userIds, NotificationMessageData $message): Collection
    {
        $recipientIds = collect($userIds)
            ->map(static fn (mixed $userId): int => (int) $userId)
            ->filter()
            ->unique()
            ->values();

        return DB::transaction(fn (): Collection => $recipientIds->map(
            fn (int $userId): Notification => $this->sendToUser($userId, $message),
        ));
    }

    /**
     * @return Collection<int, Notification>
     */
    public function sendToAllUsers(NotificationMessageData $message): Collection
    {
        $userIds = User::query()
            ->where('status', UserStatus::Active->value)
            ->pluck('id')
            ->all();

        return $this->sendToUsers($userIds, $message);
    }

    public function deleteOldNotifications(int $days): int
    {
        return $this->notifications->deleteOlderThan(max(1, $days));
    }
}
