<?php

namespace App\Modules\Notifications\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Base\Http\Responses\ApiResponse;
use App\Modules\Notifications\Http\Resources\V1\NotificationCollection;
use App\Modules\Notifications\Http\Resources\V1\NotificationResource;
use App\Modules\Notifications\Http\Services\Api\V1\UserNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly UserNotificationService $notifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(50, max(1, $request->integer('per_page', 15)));
        $notifications = $this->notifications->paginate($this->notifiable(), $perPage);

        return ApiResponse::success(
            200,
            __('notifications.messages.fetched'),
            new NotificationCollection($notifications),
        );
    }

    public function unread(): JsonResponse
    {
        return ApiResponse::success(
            200,
            __('notifications.messages.fetched'),
            NotificationResource::collection($this->notifications->unread($this->notifiable())),
        );
    }

    public function unreadCount(): JsonResponse
    {
        return ApiResponse::success(200, '', [
            'unread_count' => $this->notifications->unreadCount($this->notifiable()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(200, '', new NotificationResource(
            $this->notifications->findForNotifiable($this->notifiable(), $id),
        ));
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = $this->notifications->markAsRead($this->notifiable(), $id);

        return ApiResponse::success(
            200,
            __('notifications.messages.marked_as_read'),
            new NotificationResource($notification->fresh()),
        );
    }

    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notifications->markAllAsRead($this->notifiable());

        return ApiResponse::success(
            200,
            __('notifications.messages.all_marked_as_read', ['count' => $count]),
            ['marked_count' => $count],
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->notifications->delete($this->notifiable(), $id);

        return ApiResponse::success(200, __('notifications.messages.deleted_successfully'));
    }

    public function deleteRead(): JsonResponse
    {
        $count = $this->notifications->deleteRead($this->notifiable());

        return ApiResponse::success(
            200,
            __('notifications.messages.read_deleted', ['count' => $count]),
            ['deleted_count' => $count],
        );
    }

    private function notifiable(): Model
    {
        $notifiable = auth('api')->user();

        if ($notifiable === null) {
            abort(401);
        }

        return $notifiable;
    }
}
