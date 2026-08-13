<?php

namespace Database\Seeders\Demo\Concerns;

use Database\Seeders\Demo\DemoImageLibrary;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Shared by every Demo\* seeder — one safe way to attach a demo image to
 * any Spatie HasMedia model. Never overwrites an already-attached asset
 * (an admin — or an earlier, real upload — may already own that
 * collection), which is what makes every seeder in this pass safe to
 * re-run against a database that already has real content in it.
 */
trait SeedsMedia
{
    protected function attachImage(
        HasMedia $model,
        string $collection,
        DemoImageLibrary $images,
        string $imageKey,
        ?string $altText = null,
        array $customProperties = []
    ): ?Media {
        if ($model->getMedia($collection)->isNotEmpty()) {
            return null;
        }

        /** @var Media $media */
        $media = $model->addMedia($images->path($imageKey))
            ->preservingOriginal()
            ->usingName($images->label($imageKey))
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);

        if ($altText) {
            $media->forceFill(['alt_text' => $altText])->save();
        }

        return $media;
    }

    /**
     * For multi-item collections (galleries) — guards the *whole* batch
     * with one upfront emptiness check, not a per-item one. A per-item
     * check is a real bug, not just a style choice: Spatie refreshes a
     * model's loaded media collection immediately after `addMedia()`,
     * so checking `isEmpty()` again inside the same loop sees the item
     * just added and silently skips every key after the first — even on
     * a single, first run.
     */
    protected function attachGalleryImages(HasMedia $model, string $collection, DemoImageLibrary $images, array $imageKeys, array $altTextByKey = []): void
    {
        if ($model->getMedia($collection)->isNotEmpty()) {
            return;
        }

        foreach ($imageKeys as $key) {
            $media = $model->addMedia($images->path($key))
                ->preservingOriginal()
                ->usingName($images->label($key))
                ->toMediaCollection($collection);

            if (isset($altTextByKey[$key])) {
                $media->forceFill(['alt_text' => $altTextByKey[$key]])->save();
            }
        }
    }

    protected function attachLogo(
        HasMedia $model,
        string $collection,
        DemoImageLibrary $images,
        string $slug,
        string $label,
        string $hex,
        ?string $altText = null
    ): ?Media {
        if ($model->getMedia($collection)->isNotEmpty()) {
            return null;
        }

        /** @var Media $media */
        $media = $model->addMedia($images->logo($slug, $label, $hex))
            ->preservingOriginal()
            ->usingName("{$label} logo")
            ->toMediaCollection($collection);

        if ($altText) {
            $media->forceFill(['alt_text' => $altText])->save();
        }

        return $media;
    }

    protected function attachDocument(
        HasMedia $model,
        string $collection,
        string $localPath,
        string $displayName,
        ?string $altText = null
    ): ?Media {
        if ($model->getMedia($collection)->isNotEmpty()) {
            return null;
        }

        /** @var Media $media */
        $media = $model->addMedia($localPath)
            ->preservingOriginal()
            ->usingName($displayName)
            ->toMediaCollection($collection);

        if ($altText) {
            $media->forceFill(['alt_text' => $altText])->save();
        }

        return $media;
    }
}
