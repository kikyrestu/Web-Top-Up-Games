<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Upload\Services\UploadValidationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UploadController extends Controller
{
    public function __construct(private readonly UploadValidationService $uploadValidationService)
    {
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'context' => ['nullable', 'string', 'max:40'],
        ]);

        $context = strtoupper(trim((string) ($validated['context'] ?? 'GENERAL')));

        $scanResult = $this->uploadValidationService->scan(
            $request,
            $request->file('file'),
            $context
        );

        $status = $scanResult['verdict'] === 'REJECTED' ? 422 : 200;

        return response()->json([
            'success' => $scanResult['verdict'] !== 'REJECTED',
            'code' => 'UPLOAD_SCANNED',
            'message' => $scanResult['verdict'] === 'REJECTED'
                ? 'Upload rejected by validation policy'
                : 'Upload scanned successfully',
            'data' => [
                'context' => $context,
                'verdict' => $scanResult['verdict'],
                'reason' => $scanResult['reason'],
                'checksum' => $scanResult['checksum'],
                'mime_type' => $scanResult['mime_type'],
                'file_size' => $scanResult['file_size'],
                'dimensions' => $scanResult['dimensions'],
            ],
        ], $status);
    }
}
