<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\User;
use App\Modules\Notifications\DTOs\NotificationMessageData;
use App\Modules\Notifications\Events\NotificationSent;
use App\Modules\Notifications\Http\Services\Dashboard\NotificationService;
use App\Modules\Notifications\Notifications\PushNotification;
use App\Modules\Notifications\Services\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;

uses(RefreshDatabase::class);

function dashboardMessage(): NotificationMessageData
{
    return new NotificationMessageData(
        titleAr: 'مرحبًا',
        titleEn: 'Welcome',
        bodyAr: 'رسالة',
        bodyEn: 'Body',
    );
}

test('dashboard sendToUser pushes to devices and broadcasts, not only a database row', function () {
    Event::fake([NotificationSent::class]);
    NotificationFacade::fake();

    $user = User::factory()->create();
    $user->registerDeviceToken('device-1', 'android');

    $notification = app(NotificationService::class)->sendToUser($user->id, dashboardMessage());

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
    ]);
    NotificationFacade::assertSentTo($user, PushNotification::class);
    Event::assertDispatched(
        NotificationSent::class,
        fn (NotificationSent $event): bool => $event->notification->is($notification),
    );
});

test('push notifier files a polymorphic notification for any notifiable model', function () {
    Event::fake([NotificationSent::class]);
    NotificationFacade::fake();

    // A Manager (not on the users table) can receive notifications now that the
    // record is polymorphic — no cross-table collision, no users-table coupling.
    $manager = Manager::factory()->create();

    $notification = app(PushNotifier::class)->send($manager, 'Hi', 'Body');

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'notifiable_type' => $manager->getMorphClass(),
        'notifiable_id' => $manager->getKey(),
    ]);
});
