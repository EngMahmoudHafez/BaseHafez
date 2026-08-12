<?php

namespace App\Modules\Notifications\DTOs;

/**
 * Immutable representation of a Firebase Cloud Messaging notification, shaped for
 * the FCM HTTP v1 API. `toArray()` builds the per-device `message` envelope.
 */
readonly class FcmMessage
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(string $title, string $body, array $data = []): self
    {
        return new self($title, $body, $data);
    }

    /**
     * Build the FCM HTTP v1 request body for a single device token.
     *
     * @return array{message: array<string, mixed>}
     */
    public function toArray(string $token): array
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ],
        ];

        $data = $this->stringData();

        if ($data !== []) {
            $message['data'] = $data;
        }

        return ['message' => $message];
    }

    /**
     * FCM v1 requires every data value to be a string.
     *
     * @return array<string, string>
     */
    private function stringData(): array
    {
        return array_map(
            static fn (mixed $value): string => is_scalar($value)
                ? (string) $value
                : (string) json_encode($value, JSON_UNESCAPED_UNICODE),
            $this->data,
        );
    }
}
