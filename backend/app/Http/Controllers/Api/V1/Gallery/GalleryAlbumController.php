<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\GalleryAlbumMediaCaptionRequest;
use App\Http\Requests\Gallery\GalleryAlbumMediaRequest;
use App\Http\Requests\Gallery\GalleryAlbumRequest;
use App\Http\Resources\GalleryAlbumResource;
use App\Models\GalleryAlbum;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4. SRS Permission Matrix, "Gallery" row: Super
 * Admin/Administrator = Full; Content Editor = Create/Edit; Marketing =
 * Create/Edit; Admissions = no access. No publish() action — see
 * GalleryAlbumPolicy's docblock.
 */
class GalleryAlbumController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', GalleryAlbum::class);

        $albums = GalleryAlbum::query()
            ->withCount(['media as items_count'])
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(GalleryAlbumResource::collection($albums));
    }

    public function store(GalleryAlbumRequest $request): JsonResponse
    {
        Gate::authorize('create', GalleryAlbum::class);

        $album = GalleryAlbum::create($request->validated());

        return ApiResponse::success(new GalleryAlbumResource($album), 201);
    }

    public function show(GalleryAlbum $galleryAlbum): JsonResponse
    {
        Gate::authorize('viewAny', GalleryAlbum::class);

        return ApiResponse::success(new GalleryAlbumResource($galleryAlbum->load('media')));
    }

    public function update(GalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): JsonResponse
    {
        Gate::authorize('update', GalleryAlbum::class);

        $galleryAlbum->update($request->validated());

        return ApiResponse::success(new GalleryAlbumResource($galleryAlbum->load('media')));
    }

    /**
     * Soft delete (Database Design, Section 2.1).
     */
    public function destroy(GalleryAlbum $galleryAlbum): Response
    {
        Gate::authorize('delete', GalleryAlbum::class);

        $galleryAlbum->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/admin/gallery-albums/{id}/media — attach existing
     * Media Library items (images or videos) to this album's 'items'
     * collection, each with its own optional caption.
     */
    public function attachMedia(GalleryAlbumMediaRequest $request, GalleryAlbum $galleryAlbum): JsonResponse
    {
        Gate::authorize('update', GalleryAlbum::class);

        foreach ($request->input('items') as $item) {
            /** @var Media $media */
            $media = Media::findOrFail($item['media_id']);
            $newMedia = $media->moveKeepingCustomFields($galleryAlbum, 'items');

            if (array_key_exists('caption', $item)) {
                $newMedia->setCustomProperty('caption', $item['caption']);
                $newMedia->save();
            }
        }

        return ApiResponse::success(new GalleryAlbumResource($galleryAlbum->fresh()->load('media')));
    }

    /**
     * DELETE /api/v1/admin/gallery-albums/{id}/media/{mediaId} — remove
     * one item from the album.
     */
    public function detachMedia(GalleryAlbum $galleryAlbum, int $mediaId): Response
    {
        Gate::authorize('update', GalleryAlbum::class);

        $galleryAlbum->getMedia('items')->where('id', $mediaId)->each->delete();

        return response()->noContent();
    }

    /**
     * PUT /api/v1/admin/gallery-albums/{id}/media/{mediaId} — not in the
     * API Design document (see GalleryAlbumMediaCaptionRequest's
     * docblock). Scoped to this album's own items so one album can't
     * relabel another's item via a guessed id.
     */
    public function updateMediaCaption(GalleryAlbumMediaCaptionRequest $request, GalleryAlbum $galleryAlbum, int $mediaId): JsonResponse
    {
        Gate::authorize('update', GalleryAlbum::class);

        /** @var Media $media */
        $media = $galleryAlbum->getMedia('items')->where('id', $mediaId)->firstOrFail();
        $media->setCustomProperty('caption', $request->input('caption'));
        $media->save();

        return ApiResponse::success(new GalleryAlbumResource($galleryAlbum->fresh()->load('media')));
    }

    /**
     * GET /api/v1/gallery-albums — public, unauthenticated. "List active
     * albums with a cover image" (API Design, Section 7.4).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $albums = GalleryAlbum::active()
            ->withCount(['media as items_count'])
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(GalleryAlbumResource::collection($albums));
    }

    /**
     * GET /api/v1/gallery-albums/{slug} — public, unauthenticated.
     * "Album detail with all media items."
     */
    public function publicShow(string $slug): JsonResponse
    {
        $album = GalleryAlbum::active()->where('slug', $slug)->firstOrFail();

        return ApiResponse::success(new GalleryAlbumResource($album->load('media')));
    }
}
