<?php

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Events\NotificationSent;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Notifications\PushNotification;
use App\Modules\Notifications\Services\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\Fixtures\NotifiableUser;

uses(RefreshDatabase::class);

test('send persists pushes and broadcasts', function () {
    Event::fake([NotificationSent::class]);
    NotificationFacade::fake();

    $user = User::factory()->create();
    $notifiable = NotifiableUser::findOrFail($user->getKey());
    $notifiable->registerDeviceToken('device-token-1', 'android');

    $notification = app(PushNotifier::class)->send(
        $notifiable,
        'Welcome',
        'Thanks for joining',
        ['order_id' => 42],
    );

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'user_id' => $user->getKey(),
        'title_ar' => 'Welcome',
        'title_en' => 'Welcome',
        'body_en' => 'Thanks for joining',
        'type' => Notification::TYPE_GENERAL,
    ]);

    NotificationFacade::assertSentTo(
        $notifiable,
        PushNotification::class,
        static fn (PushNotification $push): bool => $push->title === 'Welcome'
            && $push->toFcm($notifiable)->body === 'Thanks for joining',
    );

    Event::assertDispatched(
        NotificationSent::class,
        static fn (NotificationSent $event): bool => $event->notification->is($notification)
            && $event->channelName === 'notifications.NotifiableUser.' . $user->getKey(),
    );
});

test('send skips push when recipient has no device token support', function () {
    Event::fake([NotificationSent::class]);
    NotificationFacade::fake();

    $user = User::factory()->create();

    $notification = app(PushNotifier::class)->send($user, 'Hi', 'Body');

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'user_id' => $user->getKey(),
    ]);
    NotificationFacade::assertNothingSent();
    Event::assertDispatched(NotificationSent::class);
});
