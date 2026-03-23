<?php

declare(strict_types=1);

namespace App\Domain\Upload\Services;

use App\Models\FileUploadLog;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class AdminImageUploadService
{
    public function upload(Request $request, UploadedFile $file, string $folder): string
    {
        $storedPath = $file->store($folder, 'public');
        $publicPath = '/storage/'.ltrim($storedPath, '/');

        FileUploadLog::query()->create([
            'actor_type' => 'USER',
            'actor_id' => $request->user()?->id,
            'original_name' => (string) $file->getClientOriginalName(),
            'storage_path' => $publicPath,
            'mime_type' => (string) $file->getClientMimeType(),
            'file_size' => (int) $file->getSize(),
            'sha256_checksum' => hash_file('sha256', $file->getRealPath() ?: ''),
            'upload_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'verdict' => 'ACCEPTED',
            'reason' => null,
        ]);

        return $publicPath;
    }
}
