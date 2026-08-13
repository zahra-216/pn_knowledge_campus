<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (High remediation) — FR-27 explicitly requires Open Graph
 * and Twitter Card image fields per content type; `seo_meta` had every
 * other OG/Twitter field (title/description) but no image. Bare Media
 * Library ids, no FK constraint — same soft-reference convention as
 * every other `_media_id` column in this app (Settings' `logo_media_id`,
 * PageBlock's inline image/gallery data), resolved to a URL at read
 * time rather than enforced at the database level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('og_image_media_id')->nullable()->after('og_description');
            $table->unsignedBigInteger('twitter_image_media_id')->nullable()->after('twitter_description');
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn(['og_image_media_id', 'twitter_image_media_id']);
        });
    }
};
