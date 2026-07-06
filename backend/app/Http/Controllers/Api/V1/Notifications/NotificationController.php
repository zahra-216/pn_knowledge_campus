<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Milestone 23 (Notification System) — the in-app notification bell
 * (frontend/src/layouts/AdminLayout/NotificationsPanel.tsx). Every
 * method is scoped to `$request->user()->notifications()` — a user can
 * only ever see/mark their own, no permission gate needed beyond being
 * authenticated (this mirrors how Laravel's own Notifiable relation
 * already scopes by notifiable_id/type).
 *
 * Only NewApplicationNotification writes to the 'database' channel
 * today (see its docblock) — Inquiry's own staff notification is
 * mail-only, so this inbox will only ever show application alerts until
 * a future module adds more 'database'-channel notifications.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->limit(20)->get();

        return ApiResponse::success([
            'items' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $record = $request->user()->notifications()->where('id', $notification)->firstOrFail();
        $record->markAsRead();

        return ApiResponse::success(['id' => $record->id, 'read_at' => $record->read_at]);
    }

    public function markAllRead(Request $request): Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
