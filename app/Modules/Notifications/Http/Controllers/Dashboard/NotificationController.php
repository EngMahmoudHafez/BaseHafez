<?php

namespace App\Modules\Notifications\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\DTOs\NotificationMessageData;
use App\Modules\Notifications\Http\Requests\Dashboard\BroadcastNotificationRequest;
use App\Modules\Notifications\Http\Requests\Dashboard\NotificationIndexRequest;
use App\Modules\Notifications\Http\Services\Dashboard\NotificationService;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class NotificationController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:notifications-read', only: ['index', 'show']),
            new Middleware('permission:notifications-create', only: ['broadcast']),
            new Middleware('permission:notifications-delete', only: ['destroy', 'deleteAll']),
        ];
    }

    public function index(NotificationIndexRequest $request): View
    {
        return $this->notificationService->index($request);
    }

    public function broadcast(BroadcastNotificationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $message = NotificationMessageData::fromArray($data);

        $notifications = $data['target_type'] === 'all'
            ? $this->notificationService->sendToAllUsers($message)
            : $this->notificationService->sendToUsers($data['user_ids'], $message);

        return to_route('dashboard.notifications.index')->with(
            'success',
            __('notifications.messages.broadcast_successfully', ['count' => $notifications->count()]),
        );
    }

    public function show(Notification $notification): View
    {
        $notification->load('notifiable');

        return view('notifications::dashboard.show', compact('notification'));
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return to_route('dashboard.notifications.index')
            ->with('success', __('notifications.messages.deleted_successfully'));
    }

    public function deleteAll(): RedirectResponse
    {
        $count = Notification::query()->delete();

        return to_route('dashboard.notifications.index')
            ->with('success', __('notifications.messages.all_deleted_successfully', ['count' => $count]));
    }
}
