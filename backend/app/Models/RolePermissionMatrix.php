<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class RolePermissionMatrix extends Model
{
    use HasFactory;

    protected $table = 'role_permission_matrix';

    protected $fillable = [
        'role',
        'permission_key',
        'is_allowed',
    ];

    protected function casts(): array
    {
        return [
            'is_allowed' => 'boolean',
        ];
    }
}
