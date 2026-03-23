<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CmsBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'position',
        'image_path',
        'target_url',
        'start_at',
        'end_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
