<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CmsHomepageBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_key',
        'block_type',
        'title',
        'subtitle',
        'body',
        'image_path',
        'target_url',
        'payload',
        'start_at',
        'end_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
