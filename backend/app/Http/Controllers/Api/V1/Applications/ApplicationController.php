<?php

namespace App\Http\Controllers\Api\V1\Applications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applications\ApplicationCreateRequest;
use App\Http\Requests\Applications\ApplicationUpdateRequest;
use App\Http\Requests\Applications\UploadApplicationDocumentRequest;
use App\Http\Resources\ApplicationDocumentResource;
use App\Http\Resources\ApplicationPublicResource;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationReceivedNotification;
use App\Notifications\ApplicationStatusNotification;
use App\Notifications\NewApplicationNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Milestone 20 (Online Applications). One controller for both halves of
 * this module — visitor-facing (public, unauthenticated) and admin
 * (authenticated, applications.* gated) — same one-controller-per-
 * resource convention used throughout this project (e.g. PartnerController
 * has both admin CRUD and publicIndex).
 *
 * The visitor half has no account/login to check against, so every
 * mutating visitor action re-verifies "does the email in this request
 * match the email on file for this application_number" (see
 * authorizeOwner()) rather than any session/token — see Application's
 * migration docblock for why this is the deliberate design, not a
 * placeholder.
 */
class ApplicationController extends Controller
{
    // ------------------------------------------------------------------
    // Visitor-facing (public, unauthenticated)
    // ------------------------------------------------------------------

    public function store(ApplicationCreateRequest $request): JsonResponse
    {
        $application = Application::create([
            ...$request->validated(),
            // Explicit rather than left absent-when-unchecked, so the
            // create response's in-memory model matches what a later
            // fetch would show (the column has a DB default of false,
            // but an unset attribute reads as null until refreshed).
            'international_applicant' => $request->boolean('international_applicant'),
            'status' => 'draft',
        ]);

        return ApiResponse::success(new ApplicationPublicResource($application->load('course')), 201);
    }

    /**
     * POST /api/v1/applications/lookup — resumes a draft/submitted
     * application. 404 (not 403) on a mismatch either way, so a guess
     * at an application_number can't be used to confirm it exists.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'application_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $application = Application::where('application_number', $request->string('application_number'))->first();

        if (! $application || ! $application->ownedBy($request->string('email'))) {
            abort(404, 'No application found matching that reference number and email.');
        }

        return ApiResponse::success(new ApplicationPublicResource($application->load('course')));
    }

    public function update(ApplicationUpdateRequest $request, Application $application): JsonResponse
    {
        $this->authorizeOwner($application, $request->string('email'));
        $this->ensureEditable($application);

        $data = $request->safe()->except(['email', 'new_email']);
        if ($request->filled('new_email')) {
            $data['email'] = $request->string('new_email');
        }

        $application->update($data);

        return ApiResponse::success(new ApplicationPublicResource($application->fresh('course')));
    }

    public function uploadDocument(UploadApplicationDocumentRequest $request, Application $application): JsonResponse
    {
        $this->authorizeOwner($application, $request->string('email'));
        $this->ensureEditable($application);

        $media = $application->addMedia($request->file('file'))->toMediaCollection('documents');
        $media->setCustomProperty('label', $request->string('label'))->save();
        $media->application_number = $application->application_number;

        return ApiResponse::success(new ApplicationDocumentResource($media), 201);
    }

    /**
     * Scoped to this application's own documents (`$application->media()`,
     * not `Media::find()`) — same IDOR protection as
     * GalleryAlbumController's detachMedia.
     */
    public function deleteDocument(Request $request, Application $application, int $mediaId): Response
    {
        $request->validate(['email' => ['required', 'email']]);
        $this->authorizeOwner($application, $request->string('email'));
        $this->ensureEditable($application);

        $application->media()->where('id', $mediaId)->firstOrFail()->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/applications/{application_number}/documents/{mediaId}/download
     *
     * Audit fix (Critical remediation) — the one read path into a
     * private-disk 'documents' file (see the model's own docblock).
     * Deliberately reachable by two different callers instead of two
     * separate routes: a staff member with applications.view (Bearer
     * token, no ?email= needed — resolved directly via the 'sanctum'
     * guard even though this route isn't behind the auth:sanctum
     * middleware group, since guard resolution doesn't require it) or
     * the applicant themselves via the same ?email= ownership check
     * every other visitor action here uses. Unlike update/delete/submit,
     * this intentionally has no ensureEditable() call — an applicant
     * should still be able to view/download their own documents after
     * submitting, only editing them is blocked post-submission.
     */
    public function downloadDocument(Request $request, Application $application, int $mediaId): BinaryFileResponse
    {
        $staffUser = Auth::guard('sanctum')->user();

        if ($staffUser && $staffUser->can('applications.view')) {
            return $this->streamDocument($application, $mediaId);
        }

        $request->validate(['email' => ['required', 'email']]);
        $this->authorizeOwner($application, $request->string('email'));

        return $this->streamDocument($application, $mediaId);
    }

    private function streamDocument(Application $application, int $mediaId): BinaryFileResponse
    {
        $media = $application->media()->where('id', $mediaId)->firstOrFail();

        return response()->download($media->getPath(), $media->file_name);
    }

    public function submit(Request $request, Application $application): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $this->authorizeOwner($application, $request->string('email'));
        $this->ensureEditable($application);

        abort_if($application->getMedia('documents')->isEmpty(), 422, 'Please upload at least one document before submitting.');

        $application->update(['status' => 'submitted', 'submitted_at' => now()]);

        $this->sendSubmissionNotifications($application);

        return ApiResponse::success(new ApplicationPublicResource($application->fresh('course')));
    }

    private function authorizeOwner(Application $application, string $email): void
    {
        abort_unless($application->ownedBy($email), 404);
    }

    private function ensureEditable(Application $application): void
    {
        abort_if($application->status !== 'draft', 409, 'This application has already been submitted and can no longer be edited.');
    }

    // ------------------------------------------------------------------
    // Admin (authenticated, applications.* gated)
    // ------------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Application::class);

        $applications = Application::query()
            ->with(['course', 'reviewedBy'])
            ->where('status', '!=', 'draft')
            ->status($request->input('filter.status'))
            ->when($request->filled('filter.course'), fn ($q) => $q->where('course_id', $request->input('filter.course')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('application_number', 'like', "%{$term}%"));
            })
            ->orderByDesc('submitted_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ApplicationResource::collection($applications));
    }

    public function show(Application $application): JsonResponse
    {
        Gate::authorize('viewAny', Application::class);
        abort_if($application->status === 'draft', 404);

        return ApiResponse::success(new ApplicationResource($application->load(['course', 'reviewedBy'])));
    }

    public function markUnderReview(Request $request, Application $application): JsonResponse
    {
        Gate::authorize('review', Application::class);
        abort_unless($application->status === 'submitted', 409, 'Only submitted applications can be marked under review.');

        $application->update([
            'status' => 'under_review',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return ApiResponse::success(new ApplicationResource($application->fresh(['course', 'reviewedBy'])));
    }

    public function approve(Request $request, Application $application): JsonResponse
    {
        Gate::authorize('review', Application::class);
        $request->validate(['review_notes' => ['nullable', 'string', 'max:2000']]);
        $this->ensureReviewable($application);

        $application->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        $this->sendStatusNotification($application);

        return ApiResponse::success(new ApplicationResource($application->fresh(['course', 'reviewedBy'])));
    }

    public function reject(Request $request, Application $application): JsonResponse
    {
        Gate::authorize('review', Application::class);
        $request->validate(['review_notes' => ['required', 'string', 'max:2000']]);
        $this->ensureReviewable($application);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        $this->sendStatusNotification($application);

        return ApiResponse::success(new ApplicationResource($application->fresh(['course', 'reviewedBy'])));
    }

    /**
     * GET /admin/applications/export — the project's first CSV export.
     * Respects the same status/course filters as index() so staff can
     * export exactly the slice they're looking at.
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Application::class);

        $applications = Application::query()
            ->with('course')
            ->where('status', '!=', 'draft')
            ->status($request->input('filter.status'))
            ->when($request->filled('filter.course'), fn ($q) => $q->where('course_id', $request->input('filter.course')))
            ->orderBy('submitted_at')
            ->get();

        return response()->streamDownload(function () use ($applications) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Application Number', 'First Name', 'Last Name', 'Email', 'Phone', 'Course', 'Status', 'Submitted At']);

            foreach ($applications as $application) {
                fputcsv($out, [
                    $application->application_number,
                    $application->first_name,
                    $application->last_name,
                    $application->email,
                    $application->phone,
                    $application->course?->course_name,
                    $application->status,
                    $application->submitted_at,
                ]);
            }

            fclose($out);
        }, 'applications-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function ensureReviewable(Application $application): void
    {
        abort_if(in_array($application->status, ['draft', 'approved', 'rejected'], true), 409, 'This application has already reached a final decision.');
    }

    // ------------------------------------------------------------------
    // Notifications (Milestone 23) — queued (ShouldQueue), so a bad SMTP
    // config can no longer fail the request that triggered these at all;
    // the try/catch here only guards dispatch itself. NewApplicationNotification
    // goes to every real User with applications.view (mail+database, an
    // actual in-app alert) — a real improvement over Milestone 20's
    // single configured mailbox, since Applications has a real admin
    // review screen for the notification to link to (see that class's
    // docblock for why Inquiry's own staff notification stays on-demand).
    // ------------------------------------------------------------------

    private function sendSubmissionNotifications(Application $application): void
    {
        try {
            $staffUsers = User::permission('applications.view')->get();

            if ($staffUsers->isNotEmpty()) {
                Notification::send($staffUsers, new NewApplicationNotification($application));
            }

            Notification::route('mail', $application->email)->notify(new ApplicationReceivedNotification($application));
        } catch (\Throwable $e) {
            Log::warning('Application submission notification dispatch failed.', ['application_id' => $application->id, 'error' => $e->getMessage()]);
        }
    }

    private function sendStatusNotification(Application $application): void
    {
        try {
            Notification::route('mail', $application->email)->notify(new ApplicationStatusNotification($application));
        } catch (\Throwable $e) {
            Log::warning('Application status notification dispatch failed.', ['application_id' => $application->id, 'error' => $e->getMessage()]);
        }
    }
}
