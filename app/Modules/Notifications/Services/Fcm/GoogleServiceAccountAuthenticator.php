<?php

namespace App\Modules\Notifications\Services\Fcm;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

/**
 * Mints short-lived OAuth 2.0 access tokens for the FCM HTTP v1 API from a Google
 * service-account JSON file, using the JWT-bearer flow. The JWT is signed locally
 * with `openssl` (no Firebase SDK or Composer package), and successful tokens are
 * cached until shortly before they expire.
 */
class GoogleServiceAccountAuthenticator
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const DEFAULT_TOKEN_URI = 'https://oauth2.googleapis.com/token';

    private const CACHE_KEY = 'notifications.fcm.access_token';

    /**
     * Whether a usable service-account credentials file is configured.
     */
    public function isConfigured(): bool
    {
        return $this->credentials() !== null;
    }

    /**
     * A cached or freshly minted OAuth access token, or null when credentials are
     * missing/invalid or the token endpoint could not be reached.
     */
    public function accessToken(): ?string
    {
        $credentials = $this->credentials();

        if ($credentials === null) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $token = $this->requestAccessToken($credentials);

        if ($token !== null) {
            Cache::put(self::CACHE_KEY, $token, now()->addMinutes(50));
        }

        return $token;
    }

    /**
     * @return array{client_email: string, private_key: string, token_uri: string}|null
     */
    private function credentials(): ?array
    {
        $path = config('services.fcm.credentials');

        if (! is_string($path) || $path === '' || ! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            return null;
        }

        $clientEmail = $decoded['client_email'] ?? null;
        $privateKey = $decoded['private_key'] ?? null;
        $tokenUri = $decoded['token_uri'] ?? null;

        if (! is_string($clientEmail) || $clientEmail === '' || ! is_string($privateKey) || $privateKey === '') {
            return null;
        }

        return [
            'client_email' => $clientEmail,
            'private_key' => $privateKey,
            'token_uri' => is_string($tokenUri) && $tokenUri !== '' ? $tokenUri : self::DEFAULT_TOKEN_URI,
        ];
    }

    /**
     * @param  array{client_email: string, private_key: string, token_uri: string}  $credentials
     */
    private function requestAccessToken(array $credentials): ?string
    {
        $assertion = $this->buildAssertion($credentials);

        if ($assertion === null) {
            return null;
        }

        try {
            $response = Http::asForm()->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);
        } catch (Throwable $exception) {
            Log::warning('FCM OAuth token request threw an exception.', ['exception' => $exception::class]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('FCM OAuth token request failed.', ['status' => $response->status()]);

            return null;
        }

        $token = $response->json('access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * Build and RS256-sign the JWT assertion exchanged for an access token.
     *
     * @param  array{client_email: string, private_key: string, token_uri: string}  $credentials
     */
    private function buildAssertion(array $credentials): ?string
    {
        $issuedAt = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => $credentials['token_uri'],
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ];

        try {
            $segments = [
                $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
                $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR)),
            ];
        } catch (JsonException) {
            return null;
        }

        $signingInput = implode('.', $segments);
        $signature = '';

        if (openssl_sign($signingInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256) !== true) {
            Log::warning('FCM OAuth assertion could not be signed. Check the service-account private key.');

            return null;
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
