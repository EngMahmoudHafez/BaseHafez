<?php

namespace App\Modules\Notifications\Services\Fcm;

use App\Modules\Notifications\DTOs\FcmMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a message to one or more device tokens through the FCM HTTP v1 API using
 * Laravel's HTTP client. Delivery is skipped silently when FCM is not fully
 * configured, so local and testing environments never break without credentials.
 */
class FcmClient
{
    private const ENDPOINT = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    public function __construct(
        private readonly GoogleServiceAccountAuthenticator $authenticator,
    ) {}

    /**
     * @param  array<int, string>  $tokens
     */
    public function send(FcmMessage $message, array $tokens): void
    {
        $projectId = config('services.fcm.project_id');

        if ($tokens === [] || ! is_string($projectId) || $projectId === '' || ! $this->authenticator->isConfigured()) {
            return;
        }

        $accessToken = $this->authenticator->accessToken();

        if ($accessToken === null) {
            return;
        }

        $endpoint = sprintf(self::ENDPOINT, $projectId);

        foreach ($tokens as $token) {
            $this->deliver($endpoint, $accessToken, $message, $token);
        }
    }

    private function deliver(string $endpoint, string $accessToken, FcmMessage $message, string $token): void
    {
        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(2, 200, throw: false)
                ->post($endpoint, $message->toArray($token));

            if ($response->failed()) {
                Log::warning('FCM push notification failed.', [
                    'status' => $response->status(),
                    'error' => $response->json('error.status'),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('FCM push notification threw an exception.', [
                'exception' => $exception::class,
            ]);
        }
    }
}
