<?php

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Notifications\PushNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\NotifiableUser;

uses(RefreshDatabase::class);

test('register creates a device token', function () {
    $user = notifiable();

    $token = $user->registerDeviceToken('abc-123', 'ios');

    $this->assertDatabaseHas('device_tokens', [
        'id' => $token->id,
        'token' => 'abc-123',
        'platform' => 'ios',
        'tokenable_id' => $user->getKey(),
        'tokenable_type' => $user->getMorphClass(),
    ]);
    expect($token->last_used_at)->not->toBeNull();
});

test('registering an existing token updates it and reassigns the owner', function () {
    $first = notifiable();
    $second = notifiable();

    $first->registerDeviceToken('shared-token', 'android');
    $second->registerDeviceToken('shared-token', 'web');

    $this->assertDatabaseCount('device_tokens', 1);
    $this->assertDatabaseHas('device_tokens', [
        'token' => 'shared-token',
        'platform' => 'web',
        'tokenable_id' => $second->getKey(),
    ]);
});

test('forget removes the device token', function () {
    $user = notifiable();
    $user->registerDeviceToken('to-be-removed');

    $user->forgetDeviceToken('to-be-removed');

    $this->assertDatabaseMissing('device_tokens', ['token' => 'to-be-removed']);
});

test('route notification for fcm returns only the recipients tokens', function () {
    $user = notifiable();
    $other = notifiable();
    $user->registerDeviceToken('mine-1');
    $user->registerDeviceToken('mine-2');
    $other->registerDeviceToken('theirs');

    $tokens = $user->routeNotificationForFcm(new PushNotification('title', 'body'));

    expect($tokens->all())->toEqualCanonicalizing(['mine-1', 'mine-2']);
});

function notifiable(): NotifiableUser
{
    return NotifiableUser::findOrFail(User::factory()->create()->getKey());
}
