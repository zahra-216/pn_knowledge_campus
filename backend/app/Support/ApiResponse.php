<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Every JSON response in this API is built through this class, so the
 * envelope shape defined in the API Design document (Section 4 — Standard
 * Response Format, Section 6 — Error Responses) is guaranteed rather than
 * re-implemented per controller.
 *
 * Usage:
 *   ApiResponse::success($resource);
 *   ApiResponse::success($resource, 201);
 *   ApiResponse::message('Signed out.');
 *   ApiResponse::error('Course not found.', 404);
 *   ApiResponse::validationError(['email' => ['Required.']]);
 */
class ApiResponse
{
    /**
     * A single resource or resource collection, wrapped in { "data": ... }.
     * Pagination meta/links are added automatically by Laravel when the
     * underlying resource is a paginated collection.
     */
    public static function success(JsonResource|ResourceCollection|array|null $payload = null, int $status = 200): JsonResponse
    {
        if ($payload instanceof JsonResource || $payload instanceof ResourceCollection) {
            return $payload->response()->setStatusCode($status);
        }

        return response()->json(['data' => $payload], $status);
    }

    /**
     * A response with no resource payload, just a human-readable message
     * (e.g. logout, delete confirmations).
     */
    public static function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    /**
     * Standard error envelope: { "message": "..." }.
     * Optional $extra merges in additional keys (e.g. required_permission,
     * conflict) as documented per error type in the API Design document.
     */
    public static function error(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(array_merge(['message' => $message], $extra), $status);
    }

    /**
     * 422 Validation error envelope: { "message": "...", "errors": {...} }.
     */
    public static function validationError(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}
