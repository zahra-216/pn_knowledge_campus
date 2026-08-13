<?php

namespace App\Http\Controllers\Api\V1\Inquiries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inquiries\AddInquiryNoteRequest;
use App\Http\Requests\Inquiries\AssignInquiryRequest;
use App\Http\Requests\Inquiries\InquiryRequest;
use App\Http\Requests\Inquiries\UpdateInquiryStatusRequest;
use App\Http\Resources\InquiryAdminResource;
use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\InquiryNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
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

        InquiryNotifier::send($inquiry);

        return ApiResponse::success(new InquiryResource($inquiry), 201);
    }

    // ------------------------------------------------------------------
    // Admin (authenticated, inquiries.* gated)
    // ------------------------------------------------------------------

    /**
     * GET /api/v1/admin/inquiries/assignable-staff — audit fix (High
     * remediation), backs the assignment dropdown. Deliberately not the
     * general /admin/users list: that's gated by users.view, which only
     * Super Admin holds (see UserPolicy) — Administrator/Admissions
     * both need to assign inquiries to each other without needing
     * Super-Admin-only user-management access. Scoped to whoever
     * actually holds inquiries.manage, the same population "assign to a
     * staff member" means in practice.
     */
    public function assignableStaff(): JsonResponse
    {
        Gate::authorize('manage', Inquiry::class);

        $staff = User::permission('inquiries.manage')->orderBy('name')->get(['id', 'name']);

        return ApiResponse::success($staff->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])->all());
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Inquiry::class);

        $inquiries = Inquiry::query()
            ->with(['course', 'assignedTo'])
            ->when($request->filled('filter.status'), fn ($q) => $q->where('status', $request->input('filter.status')))
            ->when($request->filled('filter.course'), fn ($q) => $q->where('course_id', $request->input('filter.course')))
            ->when($request->has('filter.assigned_to'), function ($query) use ($request) {
                // filter[assigned_to]=0 means "unassigned" — a plain
                // `?filled()` check would treat "0" as empty and skip it.
                $value = $request->input('filter.assigned_to');
                $value ? $query->where('assigned_to', $value) : $query->whereNull('assigned_to');
            })
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

        return ApiResponse::success(new InquiryAdminResource($inquiry->load(['course', 'assignedTo', 'notes.author'])));
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('manage', Inquiry::class);

        $inquiry->update(['status' => $request->validated('status')]);

        return ApiResponse::success(new InquiryAdminResource($inquiry->fresh(['course', 'assignedTo', 'notes.author'])));
    }

    /**
     * PATCH /api/v1/admin/inquiries/{inquiry}/assign — audit fix (High
     * remediation). `assigned_to: null` unassigns; both the SRS
     * ("optional assignment to a staff member") and the Database Design
     * document specify this as part of the Inquiry Management module,
     * never implemented until now.
     */
    public function assign(AssignInquiryRequest $request, Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('manage', Inquiry::class);

        $inquiry->update(['assigned_to' => $request->validated('assigned_to')]);

        return ApiResponse::success(new InquiryAdminResource($inquiry->fresh(['course', 'assignedTo', 'notes.author'])));
    }

    /**
     * POST /api/v1/admin/inquiries/{inquiry}/notes — audit fix (High
     * remediation). The Database Design document's other missing half
     * of this module — a staff follow-up note thread, never implemented
     * until now. Notes are create-only (no edit/delete endpoint) —
     * consistent with an audit-trail-style log of what staff did, not an
     * editable document.
     */
    public function addNote(AddInquiryNoteRequest $request, Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('manage', Inquiry::class);

        $inquiry->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return ApiResponse::success(new InquiryAdminResource($inquiry->fresh(['course', 'assignedTo', 'notes.author'])), 201);
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
            // Audit fix (Medium remediation) — see ApplicationController::export()'s
            // identical fix; this export previously ignored the same
            // `search` param index() applies.
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%"));
            })
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
}
