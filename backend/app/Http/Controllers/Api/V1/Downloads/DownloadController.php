<?php

namespace App\Http\Controllers\Api\V1\Downloads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Downloads\AttachDownloadRequest;
use App\Http\Requests\Downloads\DownloadRequest;
use App\Http\Requests\Downloads\RequestDownloadRequest;
use App\Http\Resources\DownloadResource;
use App\Models\Download;
use App\Models\DownloadAttachment;
use App\Models\Inquiry;
use App\Models\Media;
use App\Support\ApiResponse;
use App\Support\InquiryNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Milestone 18 — the standalone Downloads catalog (Prospectus,
 * Application Forms, Brochures, and other public documents). One file
 * per row (see Download::registerMediaCollections()), same pattern as
 * PartnerController's single 'logo' collection.
 *
 * `requestDownload()`/`serveFile()` and `attach()`/`detach()` are an
 * audit fix (High remediation) completing FR-06's gated-download
 * capture flow and the Database Design document's cross-entity reuse
 * pivot — see Download model's own docblock.
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
        } elseif ($request->has('requires_inquiry')) {
            $this->syncMediaDisk($download);
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

    /**
     * POST /api/v1/downloads/{download}/request — public, unauthenticated.
     * FR-06's "prospectus download gated by a short form": when
     * `requires_inquiry` is set, this logs the name/email as a real
     * Inquiry (same notifications a Contact-form submission gets, via
     * InquiryNotifier) and returns a signed, 30-minute URL — the actual
     * file is otherwise unreachable (see DownloadResource's docblock on
     * why `file_url` is withheld from the public response for these).
     * Ungated downloads already have a working `file_url` directly on
     * the public resource and don't need this endpoint at all, but it's
     * safe to call anyway (skips the Inquiry, just returns the URL).
     *
     * `download_count` is only reliably incremented for requests that
     * go through this endpoint — an ungated file's direct `file_url` is
     * a plain static link with no ping, so its clicks aren't counted.
     * Tracking every anonymous direct-link click would need its own
     * lightweight ping mechanism (like PageView's), which is out of
     * scope for what this fix is actually about (the gate, not
     * analytics).
     */
    public function requestDownload(RequestDownloadRequest $request, Download $download): JsonResponse
    {
        abort_if(! $download->getFirstMedia('file'), 404, 'This download has no file attached yet.');

        if ($download->requires_inquiry) {
            $inquiry = Inquiry::create([
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'phone' => $request->input('phone'),
                'message' => "Requested a download: {$download->title}",
                'source_page' => "Downloads: {$download->title}",
                'status' => 'new',
            ]);

            InquiryNotifier::send($inquiry);
        }

        $download->increment('download_count');

        $url = $download->requires_inquiry
            ? URL::temporarySignedRoute('downloads.file', now()->addMinutes(30), ['download' => $download->id])
            : $download->getFirstMediaUrl('file');

        return ApiResponse::success(['url' => $url]);
    }

    /**
     * GET /api/v1/downloads/{download}/file — named 'downloads.file'.
     * Only reachable with a valid signature from requestDownload()
     * above; there is no unsigned way to reach a gated download's file.
     */
    public function serveFile(Request $request, Download $download): BinaryFileResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'This download link has expired or is invalid. Please request it again.');

        $media = $download->getFirstMedia('file');
        abort_if(! $media, 404);

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * GET /api/v1/admin/downloads/{download}/file — staff preview/manage
     * route for a gated download's file. Sanctum-authenticated (this
     * whole route sits inside the admin group's auth:sanctum middleware),
     * so no signature is needed the way the public serveFile() above
     * requires — see DownloadResource's docblock for why admin's
     * file_url points here instead of a broken getUrl() call.
     */
    public function previewFile(Download $download): BinaryFileResponse
    {
        Gate::authorize('viewAny', Download::class);

        $media = $download->getFirstMedia('file');
        abort_if(! $media, 404);

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * POST /api/v1/admin/downloads/{download}/attach — reuses one
     * catalog entry across multiple Course/Page sections without
     * duplicating the file (Database Design's documented reuse pivot).
     */
    public function attach(AttachDownloadRequest $request, Download $download): JsonResponse
    {
        Gate::authorize('update', Download::class);

        $attachment = $download->attachments()->firstOrCreate([
            'attachable_type' => $request->validated('attachable_type'),
            'attachable_id' => $request->validated('attachable_id'),
        ]);

        return ApiResponse::success([
            'id' => $attachment->id,
            'attachable_type' => $attachment->attachable_type,
            'attachable_id' => $attachment->attachable_id,
        ], 201);
    }

    /** DELETE /api/v1/admin/downloads/{download}/attach/{attachableType}/{attachableId} */
    public function detach(Download $download, string $attachableType, int $attachableId): Response
    {
        Gate::authorize('update', Download::class);

        DownloadAttachment::where('download_id', $download->id)
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->delete();

        return response()->noContent();
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

    /**
     * Toggling `requires_inquiry` on a download that already has a file
     * must move the file to the matching disk too — otherwise a
     * newly-gated download's file stays reachable at its old 'public'
     * URL (or a newly-ungated one stays stuck on the private disk with
     * no direct link), silently defeating Download::registerMediaCollections()'s
     * per-instance disk choice above.
     */
    private function syncMediaDisk(Download $download): void
    {
        $media = $download->getFirstMedia('file');

        if (! $media) {
            return;
        }

        $desiredDisk = $download->requires_inquiry ? 'local' : 'public';

        if ($media->disk !== $desiredDisk) {
            $media->move($download, 'file', $desiredDisk);
        }
    }
}
