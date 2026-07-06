<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaReplaceRequest;
use App\Http\Requests\Media\MediaUpdateRequest;
use App\Http\Requests\Media\MediaUploadRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaUploadService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.6. SRS Section 7.3 — folder-based organization,
 * search by filename/tag/alt text, reusable across modules.
 *
 * The "409 if still attached to published content" delete guard from the
 * API Design document is not implemented yet: in Milestone 1 every media
 * row is owned by the internal MediaLibrary singleton, never by a real
 * content record with a publish state, so that check has nothing to
 * evaluate against until Milestone 2+ content models exist. It activates
 * naturally once media starts getting re-parented onto real content.
 */
class MediaController extends Controller
{
    private const TYPE_DOCUMENT_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(private readonly MediaUploadService $uploadService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);

        $query = Media::query();

        if ($folderId = $request->input('filter.folder')) {
            $query->where('folder_id', $folderId);
        }

        if ($collection = $request->input('filter.collection')) {
            $query->where('collection_name', $collection);
        }

        if ($type = $request->input('filter.type')) {
            match ($type) {
                'image' => $query->where('mime_type', 'like', 'image/%'),
                'video' => $query->where('mime_type', 'like', 'video/%'),
                'document' => $query->whereIn('mime_type', self::TYPE_DOCUMENT_MIMES),
                default => null,
            };
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $media = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success(MediaResource::collection($media));
    }

    public function store(MediaUploadRequest $request): JsonResponse
    {
        Gate::authorize('create', Media::class);

        $media = $this->uploadService->upload(
            file: $request->file('file'),
            folderId: $request->integer('folder_id') ?: null,
            altText: $request->input('alt_text'),
            collectionName: $request->input('collection_name'),
            uploadedBy: $request->user()->id,
        );

        return ApiResponse::success(new MediaResource($media), 201);
    }

    public function update(MediaUpdateRequest $request, Media $medium): JsonResponse
    {
        Gate::authorize('update', Media::class);

        $medium->fill($request->validated())->save();

        return ApiResponse::success(new MediaResource($medium));
    }

    public function destroy(Media $medium): Response
    {
        Gate::authorize('delete', Media::class);

        $medium->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/admin/media/{id}/replace — Media Library hardening.
     * The response's `id` is a new record (see MediaUploadService::
     * replace()'s docblock for why); `replaced_media_id` tells the
     * caller which row it superseded, so the frontend can swap it in
     * place in local state without a full refetch.
     */
    public function replace(MediaReplaceRequest $request, Media $medium): JsonResponse
    {
        Gate::authorize('update', Media::class);

        $replacedId = $medium->id;

        $newMedia = $this->uploadService->replace(
            existing: $medium,
            file: $request->file('file'),
            altText: $request->input('alt_text'),
            uploadedBy: $request->user()->id,
        );

        return ApiResponse::success([
            ...(new MediaResource($newMedia))->toArray($request),
            'replaced_media_id' => $replacedId,
        ]);
    }

    /**
     * GET /api/v1/media/resolve?ids=1,2,3 — public, unauthenticated.
     * Not in the API Design document. Settings (logo_media_id,
     * favicon_media_id, welcome_media_id, default_og_image_media_id) and
     * PageBlock.data (hero/image/gallery/video blocks' media_id fields)
     * all store bare Media Library ids with no public way to resolve
     * them to a URL — every other public entity resource (Faculty,
     * Course, ...) embeds its own media as a resolved URL directly, but
     * these two spots store a raw polymorphic reference the public
     * frontend has to look up itself. Read-only, no admin-only fields
     * beyond what MediaResource already exposes.
     */
    public function publicResolve(Request $request): JsonResponse
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        $media = Media::query()->whereIn('id', $ids)->get();

        return ApiResponse::success(MediaResource::collection($media));
    }
}
