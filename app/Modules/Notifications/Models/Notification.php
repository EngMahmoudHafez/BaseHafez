<?php

namespace App\Modules\Notifications\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\database\factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title_ar
 * @property string|null $title_en
 * @property string $body_ar
 * @property string|null $body_en
 * @property string $type
 * @property array<string, mixed>|null $data
 * @property string|null $action_url
 * @property string|null $icon
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $title
 * @property-read string $body
 * @property-read User $user
 */
class Notification extends Model
{
    use HasFactory;

    public const TYPE_GENERAL = 'general';

    public const TYPE_SYSTEM = 'system';

    public const TYPE_PROMOTION = 'promotion';

    protected $fillable = [
        'user_id',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'type',
        'data',
        'action_url',
        'icon',
        'is_read',
        'read_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getTitleAttribute(): string
    {
        return app()->isLocale('ar')
            ? $this->title_ar
            : ($this->title_en ?: $this->title_ar);
    }

    public function getBodyAttribute(): string
    {
        return app()->isLocale('ar')
            ? $this->body_ar
            : ($this->body_en ?: $this->body_ar);
    }

    public function markAsRead(): self
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return $this;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_GENERAL => __('notifications.types.general'),
            self::TYPE_SYSTEM => __('notifications.types.system'),
            self::TYPE_PROMOTION => __('notifications.types.promotion'),
        ];
    }
}
