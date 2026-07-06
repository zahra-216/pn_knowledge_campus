<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Development Roadmap, Milestone 1 — "MediaUploadService (validation,
 * conversions, alt-text enforcement)", extended in the Media Library
 * hardening pass with file replacement, original-file optimization, and
 * image dimension capture.
 */
class MediaUploadService
{
    public function upload(
        UploadedFile $file,
        ?int $folderId,
        ?string $altText,
        ?string $collectionName,
        ?int $uploadedBy
    ): Media {
        $root = MediaLibrary::query()->findOrFail(1);

        /** @var Media $media */
        $media = $root
            ->addMedia($file)
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->toMediaCollection($collectionName ?: 'library');

        $media->forceFill([
            'folder_id' => $folderId,
            'alt_text' => $altText,
            'uploaded_by' => $uploadedBy,
        ]);

        $this->captureImageDimensions($media);
        $media->save();

        $this->optimizeOriginal($media);

        return $media;
    }

    /**
     * Media Library hardening — "Replace files". Spatie Media Library has
     * no API for swapping a file's bytes while keeping the same row id:
     * even the package's own official replace pattern (a singleFile()
     * collection) works by deleting the old row and creating a new one —
     * there is no "same id" replace in this ecosystem. This mirrors that:
     * the old row (and its physical files/conversions) is deleted, a new
     * row is created via the normal upload path, and the old row's
     * folder/collection are carried over so the replacement lands in the
     * same place. Callers that need a stable reference across a replace
     * should key off (owner model, collection name), which is exactly
     * the polymorphic attachment pattern the Database Design document
     * already specifies for content modules — never a raw media id.
     */
    public function replace(Media $existing, UploadedFile $file, ?string $altText, ?int $uploadedBy): Media
    {
        $folderId = $existing->folder_id;
        $collectionName = $existing->collection_name;
        $resolvedAltText = $altText ?? $existing->alt_text;

        $existing->delete();

        return $this->upload($file, $folderId, $resolvedAltText, $collectionName, $uploadedBy);
    }

    /**
     * Stores width/height in Spatie's package-standard custom_properties
     * JSON column (no schema change) — SRS Section 7.3's "file metadata"
     * requirement, for images only.
     */
    private function captureImageDimensions(Media $media): void
    {
        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $path = $media->getPath();
        $dimensions = @getimagesize($path);

        if ($dimensions !== false) {
            $media->setCustomProperty('width', $dimensions[0]);
            $media->setCustomProperty('height', $dimensions[1]);
        }
    }

    /**
     * Optimizes the original uploaded file in place (not just the 'thumb'/
     * 'web' derived conversions). Only meaningful on a local filesystem
     * disk — spatie/image-optimizer shells out to binaries against a real
     * path, which a remote disk (S3) does not expose, so this is skipped
     * there rather than failing; the conversions still run through
     * Spatie's own conversion pipeline regardless of disk.
     */
    private function optimizeOriginal(Media $media): void
    {
        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        if (config("filesystems.disks.{$media->disk}.driver") !== 'local') {
            return;
        }

        OptimizerChainFactory::create()->optimize($media->getPath());
    }
}
