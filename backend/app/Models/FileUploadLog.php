<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FileUploadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'original_name',
        'storage_path',
        'mime_type',
        'file_size',
        'sha256_checksum',
        'upload_ip',
        'user_agent',
        'verdict',
        'reason',
    ];
}
