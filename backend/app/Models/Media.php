<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Extends Spatie Media Library's own Media model with the three custom
 * columns from Database Design, Section 4.3: folder_id, alt_text,
 * uploaded_by. Registered as the package's model via
 * config/media-library.php's `media_model` key.
 */
class Media extends SpatieMedia
{
    // Deliberately no $fillable override here: Spatie's base Media model
    // already controls mass-assignment for its own package-managed
    // columns, and this class must not narrow that. The three custom
    // columns (folder_id, alt_text, uploaded_by) are set via forceFill()
    // in MediaUploadService, which bypasses mass-assignment guarding
    // entirely, so no fillable entry is needed for them either.

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * "Everything should integrate with future modules" — the mechanism
     * a real content model (Course, Page, ...) uses to claim a Media
     * Library asset. Wraps Spatie's move() (which itself works via
     * copy()+forceDelete(), so it returns a *new* row — see the
     * MediaUploadService::replace() docblock for why no Spatie
     * operation preserves a row's id across a content or ownership
     * change) because move()/copy() only preserve Spatie's own fields
     * (name, order, manipulations, custom_properties) — our three
     * custom columns are plain columns Spatie doesn't know about, so
     * they're silently dropped unless carried over explicitly here.
     * Without this wrapper, re-parenting an asset would quietly lose
     * its alt text — an accessibility/SEO regression, not a cosmetic one.
     */
    public function moveKeepingCustomFields(HasMedia $newOwner, string $collectionName): self
    {
        $altText = $this->alt_text;
        $folderId = $this->folder_id;
        $uploadedBy = $this->uploaded_by;

        /** @var self $newMedia */
        $newMedia = $this->move($newOwner, $collectionName);

        $newMedia->forceFill([
            'alt_text' => $altText,
            'folder_id' => $folderId,
            'uploaded_by' => $uploadedBy,
        ])->save();

        return $newMedia;
    }
}
