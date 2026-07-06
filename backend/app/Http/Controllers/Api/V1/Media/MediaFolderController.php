<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaFolderRequest;
use App\Http\Resources\MediaFolderResource;
use App\Models\MediaFolder;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.6. Gated by MediaPolicy (media.* permissions) —
 * the SRS Permission Matrix has one "Media Library" row covering both
 * files and their folder organization.
 */
class MediaFolderController extends Controller
{
    /**
     * GET /api/v1/admin/media-folders — the full folder tree (top-level
     * folders with children eager-loaded).
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', MediaFolder::class);

        $folders = MediaFolder::whereNull('parent_id')
            ->withCount('media')
            ->with(['children' => fn ($q) => $q->withCount('media')])
            ->orderBy('name')
            ->get();

        return ApiResponse::success(MediaFolderResource::collection($folders));
    }

    public function store(MediaFolderRequest $request): JsonResponse
    {
        Gate::authorize('create', MediaFolder::class);

        $folder = MediaFolder::create($request->validated());

        return ApiResponse::success(new MediaFolderResource($folder), 201);
    }

    public function update(MediaFolderRequest $request, MediaFolder $mediaFolder): JsonResponse
    {
        Gate::authorize('update', MediaFolder::class);

        $mediaFolder->update($request->validated());

        return ApiResponse::success(new MediaFolderResource($mediaFolder));
    }

    /**
     * DELETE /api/v1/admin/media-folders/{id} — 409 if it still contains
     * files or subfolders (API Design, Section 8.6). The DB-level CASCADE
     * on parent_id is a safety net for direct DB manipulation, not the
     * primary path — the application layer blocks non-empty deletes here.
     */
    public function destroy(MediaFolder $mediaFolder): JsonResponse|Response
    {
        Gate::authorize('delete', MediaFolder::class);

        $childCount = $mediaFolder->children()->count();
        $mediaCount = $mediaFolder->media()->count();

        if ($childCount > 0 || $mediaCount > 0) {
            return ApiResponse::error(
                'This folder cannot be deleted while it still contains files or subfolders.',
                409,
                ['conflict' => ['type' => 'has_dependent_records', 'related_resource' => 'media', 'count' => $childCount + $mediaCount]]
            );
        }

        $mediaFolder->delete();

        return response()->noContent();
    }
}
