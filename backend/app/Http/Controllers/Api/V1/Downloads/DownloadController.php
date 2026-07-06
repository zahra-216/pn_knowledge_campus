<?php

namespace App\Http\Controllers\Api\V1\Downloads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Downloads\DownloadRequest;
use App\Http\Resources\DownloadResource;
use App\Models\Download;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 18 — the standalone Downloads catalog (Prospectus,
 * Application Forms, Brochures, and other public documents). One file
 * per row (see Download::registerMediaCollections()), same pattern as
 * PartnerController's single 'logo' collection.
 */
class DownloadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Download::class);

        $downloads = Download::query()
            ->with('category')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderBy('order')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(DownloadResource::collection($downloads));
    }

    public function store(DownloadRequest $request): JsonResponse
    {
        Gate::authorize('create', Download::class);

        $download = Download::create($request->safe()->except('media_id'));

        $this->attachMedia($download, $request->input('media_id'));

        return ApiResponse::success(new DownloadResource($download->fresh('category')), 201);
    }

    public function show(Download $download): JsonResponse
    {
        Gate::authorize('viewAny', Download::class);

        return ApiResponse::success(new DownloadResource($download->load('category')));
    }

    public function update(DownloadRequest $request, Download $download): JsonResponse
    {
        Gate::authorize('update', Download::class);

        $download->update($request->safe()->except('media_id'));

        if ($request->has('media_id')) {
            $this->attachMedia($download, $request->input('media_id'));
        }

        return ApiResponse::success(new DownloadResource($download->fresh('category')));
    }

    public function destroy(Download $download): Response
    {
        Gate::authorize('delete', Download::class);

        $download->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/downloads — public, unauthenticated, ordered. Optional
     * ?category=slug filter.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $downloads = Download::query()
            ->active()
            ->with('category')
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($cq) => $cq->where('slug', $request->string('category')));
            })
            ->orderBy('order')
            ->get();

        return ApiResponse::success(DownloadResource::collection($downloads));
    }

    private function attachMedia(Download $download, ?int $mediaId): void
    {
        if ($mediaId === null) {
            $download->clearMediaCollection('file');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($download, 'file');
    }
}
