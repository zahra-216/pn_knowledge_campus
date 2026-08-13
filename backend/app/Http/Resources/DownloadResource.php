<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getFirstMedia('file');

        // Audit fix (High remediation) — a gated download's file lives
        // on the private 'local' disk (see Download::registerMediaCollections()),
        // which has no getUrl() to call at all (same constraint as
        // ApplicationDocumentResource's docblock explains). The public
        // response withholds file_url entirely — the visitor must go
        // through POST /downloads/{id}/request (capture form) to get a
        // signed, time-limited URL instead. Admin requests (this same
        // Resource class, per this app's one-resource-both-contexts
        // convention) get a Sanctum-authenticated preview route instead
        // of a broken getUrl() call, so staff can still preview/manage
        // the file regardless of the gate.
        $isAdminContext = $request->is('api/*/admin/*');
        $isGated = (bool) $this->requires_inquiry;

        $fileUrl = match (true) {
            ! $media => null,
            $isAdminContext && $isGated => url("/api/v1/admin/downloads/{$this->id}/file"),
            $isGated => null,
            default => $media->getUrl(),
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'file_url' => $fileUrl,
            'file_name' => $media?->file_name,
            'file_size' => $media?->size,
            'file_type' => $media?->mime_type,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'requires_inquiry' => $this->requires_inquiry,
            'download_count' => $this->download_count,
        ];
    }
}
