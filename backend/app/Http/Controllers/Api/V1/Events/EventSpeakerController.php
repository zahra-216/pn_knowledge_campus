<?php

namespace App\Http\Controllers\Api\V1\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EventSpeakerRequest;
use App\Http\Resources\EventSpeakerResource;
use App\Models\Event;
use App\Models\EventSpeaker;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Not in the API Design document — /admin/events/{event}/speakers
 * sub-resource for the client-requested Speakers feature. Mirrors
 * CourseFaqController's flat CRUD shape (no bulk reorder endpoint,
 * order is just a settable field on update).
 */
class EventSpeakerController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        Gate::authorize('viewAny', EventSpeaker::class);

        return ApiResponse::success(EventSpeakerResource::collection($event->speakers));
    }

    public function store(EventSpeakerRequest $request, Event $event): JsonResponse
    {
        Gate::authorize('update', EventSpeaker::class);

        $speaker = $event->speakers()->create($request->safe()->except('photo_media_id'));
        $this->syncPhoto($speaker, $request);

        return ApiResponse::success(new EventSpeakerResource($speaker), 201);
    }

    public function update(EventSpeakerRequest $request, Event $event, EventSpeaker $speaker): JsonResponse
    {
        Gate::authorize('update', EventSpeaker::class);

        $speaker->update($request->safe()->except('photo_media_id'));
        $this->syncPhoto($speaker, $request);

        return ApiResponse::success(new EventSpeakerResource($speaker));
    }

    public function destroy(Event $event, EventSpeaker $speaker): Response
    {
        Gate::authorize('update', EventSpeaker::class);

        $speaker->delete();

        return response()->noContent();
    }

    private function syncPhoto(EventSpeaker $speaker, EventSpeakerRequest $request): void
    {
        if (! $request->has('photo_media_id')) {
            return;
        }

        $mediaId = $request->input('photo_media_id');

        if ($mediaId === null) {
            $speaker->clearMediaCollection('photo');

            return;
        }

        /** @var Media $media */
        $media = Media::findOrFail($mediaId);
        $media->moveKeepingCustomFields($speaker, 'photo');
    }
}
