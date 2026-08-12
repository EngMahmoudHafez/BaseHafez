<?php

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\User;
use App\Modules\Notifications\DTOs\NotificationMessageData;
use App\Modules\Notifications\Http\Services\Dashboard\NotificationService;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

test('broadcast targets only active users', function () {
    $activeUser = User::factory()->create(['status' => UserStatus::Active]);
    $blockedUser = User::factory()->create(['status' => UserStatus::Blocked]);

    $notifications = app(NotificationService::class)->sendToAllUsers(message());

    expect($notifications)->toHaveCount(1);
    $this->assertDatabaseHas('notifications', ['user_id' => $activeUser->id, 'title_en' => 'Welcome']);
    $this->assertDatabaseMissing('notifications', ['user_id' => $blockedUser->id]);
});

test('api lists and updates only the authenticated users notifications', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownNotification = notificationFor($user);
    notificationFor($otherUser);
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)
        ->getJson('/web/v1/notifications')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownNotification->id)
        ->assertJsonPath('pagination.total', 1);

    $this->withToken($token)
        ->postJson("/web/v1/notifications/{$ownNotification->id}/read")
        ->assertOk()
        ->assertJsonPath('data.is_read', true);

    $this->assertDatabaseHas('notifications', [
        'id' => $ownNotification->id,
        'is_read' => true,
    ]);
});

test('api rejects access to another users notification', function () {
    $user = User::factory()->create();
    $otherNotification = notificationFor(User::factory()->create());

    $this->withToken(JWTAuth::fromUser($user))
        ->getJson("/web/v1/notifications/{$otherNotification->id}")
        ->assertNotFound();
});

function message(): NotificationMessageData
{
    return new NotificationMessageData(
        titleAr: 'مرحبًا',
        titleEn: 'Welcome',
        bodyAr: 'رسالة تجريبية',
        bodyEn: 'Test message',
    );
}

function notificationFor(User $user): Notification
{
    return app(NotificationService::class)->sendToUser($user->id, message());
}
