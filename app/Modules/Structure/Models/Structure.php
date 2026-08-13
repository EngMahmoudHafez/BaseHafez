<?php

namespace App\Modules\Structure\Models;

use App\Modules\Structure\database\factories\StructureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property array<string, mixed> $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Structure extends Model
{
    /** @use HasFactory<StructureFactory> */
    use HasFactory;

    protected $table = 'structures';

    /**
     * Only `key` and `content` are mass assignable.
     */
    protected $fillable = [
        'key',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    protected static function newFactory(): StructureFactory
    {
        return StructureFactory::new();
    }
}
