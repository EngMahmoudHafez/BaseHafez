<?php

use App\Modules\Notifications\DTOs\FcmMessage;
use App\Modules\Notifications\Services\Fcm\FcmClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('send posts message to fcm with an oauth token', function () {
    $credentialsPath = fakeServiceAccount();

    config([
        'services.fcm.project_id' => 'demo-project',
        'services.fcm.credentials' => $credentialsPath,
    ]);
    Cache::flush();

    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.fake', 'expires_in' => 3600]),
        'fcm.googleapis.com/*' => Http::response(['name' => 'projects/demo-project/messages/1']),
    ]);

    app(FcmClient::class)->send(new FcmMessage('Hi', 'There', ['order_id' => 42]), ['device-token-1']);

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), 'oauth2.googleapis.com')
        && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
        && is_string($request['assertion']) && $request['assertion'] !== '');

    Http::assertSent(static fn ($request): bool => str_contains($request->url(), 'projects/demo-project/messages:send')
        && $request->hasHeader('Authorization', 'Bearer ya29.fake')
        && data_get($request->data(), 'message.token') === 'device-token-1'
        && data_get($request->data(), 'message.notification.title') === 'Hi'
        && data_get($request->data(), 'message.notification.body') === 'There'
        && data_get($request->data(), 'message.data.order_id') === '42');

    @unlink($credentialsPath);
});

test('send is skipped when credentials are missing', function () {
    config(['services.fcm.project_id' => null, 'services.fcm.credentials' => null]);
    Http::fake();

    app(FcmClient::class)->send(new FcmMessage('Hi', 'There'), ['device-token-1']);

    Http::assertNothingSent();
});

function fakeServiceAccount(): string
{
    $key = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    openssl_pkey_export($key, $privateKey);

    $path = tempnam(sys_get_temp_dir(), 'fcm') . '.json';
    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'project_id' => 'demo-project',
        'client_email' => 'fcm@demo-project.iam.gserviceaccount.com',
        'private_key' => $privateKey,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR));

    return $path;
}
