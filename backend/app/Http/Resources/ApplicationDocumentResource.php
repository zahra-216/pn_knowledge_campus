<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 *
 * Audit fix (Critical remediation) — this collection lives on a private
 * disk (see Application::registerMediaCollections()'s docblock), so
 * there is no getUrl() to call. `application_number` is set as a
 * transient, non-persisted attribute by whichever caller builds this
 * resource (ApplicationController::uploadDocument(),
 * ApplicationResource, ApplicationPublicResource) — the same "stamp a
 * value the model doesn't own onto it in memory" pattern
 * SeoEntityResource uses for its own label column, avoiding an extra
 * query per document to resolve the owning Application.
 */
class ApplicationDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->getCustomProperty('label'),
            'file_name' => $this->file_name,
            'url' => url("/api/v1/applications/{$this->application_number}/documents/{$this->id}/download"),
            'size' => $this->size,
        ];
    }
}
