<?php

declare(strict_types=1);

namespace App\Domain\Upload\Services;

use App\Models\FileUploadLog;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class UploadValidationService
{
    /**
     * @return array{verdict:string, reason:?string, checksum:?string, mime_type:string, file_size:int, dimensions:?array{width:int,height:int}}
     */
    public function scan(Request $request, UploadedFile $file, string $context = 'GENERAL'): array
    {
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
        ];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

        $maxSizeKb = (int) config('services.upload_scan.max_size_kb', 5120);
        $maxWidth = (int) config('services.upload_scan.max_image_width', 4000);
        $maxHeight = (int) config('services.upload_scan.max_image_height', 4000);

        $mimeType = (string) $file->getClientMimeType();
        $originalName = (string) $file->getClientOriginalName();
        $fileSize = (int) $file->getSize();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $realPath = $file->getRealPath();
        $checksum = is_string($realPath) && $realPath !== '' && is_file($realPath)
            ? hash_file('sha256', $realPath)
            : null;

        $verdict = 'ACCEPTED';
        $reason = null;
        $dimensions = null;

        if (!in_array($mimeType, $allowedMimes, true)) {
            $verdict = 'REJECTED';
            $reason = 'MIME_NOT_ALLOWED';
        }

        if ($verdict === 'ACCEPTED' && !in_array($extension, $allowedExtensions, true)) {
            $verdict = 'REJECTED';
            $reason = 'EXTENSION_NOT_ALLOWED';
        }

        if ($verdict === 'ACCEPTED' && $fileSize > ($maxSizeKb * 1024)) {
            $verdict = 'REJECTED';
            $reason = 'FILE_TOO_LARGE';
        }

        $doubleExtensionPattern = '/\.(php|phtml|phar|exe|js|sh)\./i';
        if ($verdict === 'ACCEPTED' && preg_match($doubleExtensionPattern, $originalName) === 1) {
            $verdict = 'REJECTED';
            $reason = 'SUSPICIOUS_DOUBLE_EXTENSION';
        }

        if ($verdict === 'ACCEPTED' && str_starts_with($mimeType, 'image/')) {
            $sizeInfo = @getimagesize($file->getPathname());
            if ($sizeInfo === false) {
                $verdict = 'REJECTED';
                $reason = 'INVALID_IMAGE_CONTENT';
            } else {
                $width = (int) ($sizeInfo[0] ?? 0);
                $height = (int) ($sizeInfo[1] ?? 0);
                $dimensions = [
                    'width' => $width,
                    'height' => $height,
                ];

                if ($width <= 0 || $height <= 0) {
                    $verdict = 'REJECTED';
                    $reason = 'INVALID_IMAGE_DIMENSIONS';
                }

                if ($width > $maxWidth || $height > $maxHeight) {
                    $verdict = 'REJECTED';
                    $reason = 'IMAGE_DIMENSIONS_EXCEEDED';
                }
            }
        }

        if ($verdict === 'ACCEPTED' && str_starts_with($mimeType, 'image/') && is_string($realPath) && $realPath !== '') {
            $snippet = @file_get_contents($realPath, false, null, 0, 2048);
            if (is_string($snippet)) {
                $lower = strtolower($snippet);
                if (str_contains($lower, '<?php') || str_contains($lower, '<script')) {
                    $verdict = 'QUARANTINED';
                    $reason = 'SUSPICIOUS_EMBEDDED_SCRIPT';
                }
            }
        }

        FileUploadLog::query()->create([
            'actor_type' => $request->user() !== null ? 'USER' : 'SYSTEM',
            'actor_id' => $request->user()?->id,
            'original_name' => $originalName,
            'storage_path' => 'scan-only://'.$context,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'sha256_checksum' => $checksum,
            'upload_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'verdict' => $verdict,
            'reason' => $reason,
        ]);

        return [
            'verdict' => $verdict,
            'reason' => $reason,
            'checksum' => $checksum,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'dimensions' => $dimensions,
        ];
    }
}
