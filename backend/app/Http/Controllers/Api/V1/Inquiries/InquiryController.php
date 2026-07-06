<?php

namespace App\Http\Controllers\Api\V1\Inquiries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inquiries\InquiryRequest;
use App\Http\Requests\Inquiries\UpdateInquiryStatusRequest;
use App\Http\Resources\InquiryAdminResource;
use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Notifications\InquiryConfirmationNotification;
use App\Notifications\NewInquiryNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * POST /api/v1/inquiries — public, unauthenticated capture endpoint
 * (see the `inquiries` migration's docblock for the original minimal-
 * slice rationale). Milestone 19 (Contact Module) added the two Email
 * Notifications: staff get notified a new inquiry arrived, the visitor
 * gets an acknowledgement. Milestone 23 (Notification System)
 * formalized both into queued Notification classes — see
 * NewInquiryNotification's docblock for why this stays "mail to a
 * configured address" rather than "mail+database to real Users" the
 * way NewApplicationNotification works.
 *
 * The admin inbox (index/show/updateStatus/destroy/export) was the one
 * piece the migration's docblock deferred to "whichever future
 * milestone builds the inbox" — added here, gated by inquiries.* (see
 * InquiryPolicy), following the same one-controller-per-resource
 * convention as ApplicationController (public half + admin half
 * together, not split into two classes).
 */
class InquiryController extends Controller
{
    public function store(InquiryRequest $request): JsonResponse
    {
        $inquiry = Inquiry::create([
            ...$request->validated(),
            'status' => 'new',
        ]);

        $this->sendNotifications($inquiry);

        return ApiResponse::success(new InquiryResource($inquiry), 201);
    }

    // ------------------------------------------------------------------
    // Admin (authenticated, inquiries.* gated)
    // ------------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Inquiry::class);

        $inquiries = Inquiry::query()
            ->with('course')
            ->when($request->filled('filter.status'), fn ($q) => $q->where('status', $request->input('filter.status')))
            ->when($request->filled('filter.course'), fn ($q) => $q->where('course_id', $request->input('filter.course')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%"));
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(InquiryAdminResource::collection($inquiries));
    }

    public function show(Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('viewAny', Inquiry::class);

        return ApiResponse::success(new InquiryAdminResource($inquiry->load('course')));
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('manage', Inquiry::class);

        $inquiry->update(['status' => $request->validated('status')]);

        return ApiResponse::success(new InquiryAdminResource($inquiry->fresh('course')));
    }

    /** Deletes a single inquiry — the "clean up spam" action. */
    public function destroy(Inquiry $inquiry): Response
    {
        Gate::authorize('manage', Inquiry::class);

        $inquiry->delete();

        return response()->noContent();
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Inquiry::class);

        $inquiries = Inquiry::query()
            ->with('course')
            ->when($request->filled('filter.status'), fn ($q) => $q->where('status', $request->input('filter.status')))
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($inquiries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Message', 'Source Page', 'Course', 'International Applicant', 'Status', 'Submitted At']);

            foreach ($inquiries as $inquiry) {
                fputcsv($out, [
                    $inquiry->name,
                    $inquiry->email,
                    $inquiry->phone,
                    $inquiry->message,
                    $inquiry->source_page,
                    $inquiry->course?->course_name,
                    $inquiry->international_applicant ? 'Yes' : 'No',
                    $inquiry->status,
                    $inquiry->created_at?->toDateTimeString(),
                ]);
            }

            fclose($out);
        }, 'inquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Both notifications are queued (ShouldQueue) — dispatching just
     * inserts a `jobs` row, so a bad SMTP config can no longer fail this
     * request at all; any delivery failure surfaces later as a
     * `failed_jobs` row instead. The try/catch here only guards against
     * a failure in dispatch itself (e.g. a malformed notifiable).
     */
    private function sendNotifications(Inquiry $inquiry): void
    {
        try {
            $staffEmail = Setting::where('key', 'admissions_email')->value('value')
                ?: Setting::where('key', 'contact_email')->value('value');

            if ($staffEmail) {
                Notification::route('mail', $staffEmail)->notify(new NewInquiryNotification($inquiry));
            }

            Notification::route('mail', $inquiry->email)->notify(new InquiryConfirmationNotification($inquiry));
        } catch (\Throwable $e) {
            Log::warning('Inquiry notification dispatch failed.', ['inquiry_id' => $inquiry->id, 'error' => $e->getMessage()]);
        }
    }
}
