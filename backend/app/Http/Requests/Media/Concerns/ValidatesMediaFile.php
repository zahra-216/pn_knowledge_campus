<?php

namespace App\Http\Requests\Media\Concerns;

/**
 * Shared between MediaUploadRequest and MediaReplaceRequest so the
 * supported-type list (SRS Section 7.3: Images, Videos, PDF, Documents)
 * can't drift between "create" and "replace".
 */
trait ValidatesMediaFile
{
    private const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    private const DOCUMENT_MIMES = ['pdf', 'doc', 'docx'];

    private const VIDEO_MIMES = ['mp4', 'mov', 'webm'];

    private function fileRules(): array
    {
        $maxKb = (int) (config('media-library.max_file_size') / 1024);

        return [
            'required',
            'file',
            'max:'.$maxKb,
            'mimes:'.implode(',', [...self::IMAGE_MIMES, ...self::DOCUMENT_MIMES, ...self::VIDEO_MIMES]),
        ];
    }

    private function fileIsImage(): bool
    {
        $file = $this->file('file');

        return $file
            && $file->isValid()
            && in_array(strtolower($file->getClientOriginalExtension()), self::IMAGE_MIMES, true);
    }
}
