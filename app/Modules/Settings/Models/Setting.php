<?php

namespace App\Modules\Settings\Models;

use App\Modules\Settings\database\factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property bool $show_in_dashboard
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'show_in_dashboard',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'show_in_dashboard' => 'boolean',
        ];
    }

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }
}
