<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\JsonResponse;

final class CmsController extends Controller
{
    public function page(string $slug): JsonResponse
    {
        $page = CmsPage::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->first();

        if ($page === null) {
            return response()->json([
                'success' => false,
                'code' => 'CMS_PAGE_NOT_FOUND',
                'message' => 'CMS page not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'code' => 'CMS_PAGE_FOUND',
            'message' => 'CMS page loaded',
            'data' => [
                'id' => (int) $page->id,
                'title' => (string) $page->title,
                'slug' => (string) $page->slug,
                'type' => (string) $page->type,
                'content' => (string) $page->content,
                'published_at' => $page->published_at?->toISOString(),
            ],
        ]);
    }
}
