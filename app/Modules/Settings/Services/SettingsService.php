<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Read/write access to application settings, backed by the `settings` table.
 *
 * The full key/value map is cached forever under {@see self::CACHE_KEY} and read
 * from cache on every {@see self::get()} / {@see self::has()}. Any write
 * ({@see self::set()}, {@see self::forget()}) invalidates that cache so the next
 * read rehydrates from the database.
 *
 *     app(SettingsService::class)->set('support_email', 'help@example.com');
 *     app(SettingsService::class)->get('support_email', 'fallback@example.com');
 */
class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        $this->flush();
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function forget(string $key): void
    {
        Setting::query()->where('key', $key)->delete();

        $this->flush();
    }

    /**
     * The cached key/value map of every stored setting.
     *
     * @return array<string, mixed>
     */
    private function all(): array
    {
        /** @var array<string, mixed> $map */
        $map = Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );

        return $map;
    }

    private function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
