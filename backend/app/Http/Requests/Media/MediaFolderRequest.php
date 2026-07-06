<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by media.* in the controller
    }

    public function rules(): array
    {
        // The route model binding resolves 'media_folder' to a MediaFolder
        // instance (not a raw id) by the time validation runs, since
        // SubstituteBindings runs before the FormRequest is validated.
        $folderId = $this->route('media_folder')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('media_folders', 'id'),
                // A folder cannot become its own parent.
                Rule::notIn(array_filter([$folderId])),
            ],
        ];
    }
}
