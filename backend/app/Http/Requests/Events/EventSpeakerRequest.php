<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

/**
 * POST/PUT /admin/events/{id}/speakers[/{speakerId}] — not in the API
 * Design document (Speakers is a client-requested addition beyond the
 * base schema, see event_speakers migration's docblock). `photo_media_id`
 * references an already-uploaded Media Library item, same pattern as
 * every other single-image field in this project.
 */
class EventSpeakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by events.edit in the controller
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string'],
            'order' => ['sometimes', 'integer'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
