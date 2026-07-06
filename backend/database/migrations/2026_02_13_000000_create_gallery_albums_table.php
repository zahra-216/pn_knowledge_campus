<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "Standalone photo/video albums for the
 * public Gallery page. Individual images/videos are not a separate
 * child table — they attach directly to the album via the shared
 * polymorphic media table (collection: 'items'), each carrying its own
 * caption in Spatie's per-file custom_properties. This avoids a
 * redundant gallery_items table that would just duplicate what media
 * already models." No SEO — Gallery Albums is deliberately excluded
 * from the doc's seo_meta consumer list (Section 2.4/4.10), unlike
 * every other Engagement Content module.
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule, which
 * explicitly names Gallery Albums alongside Course/Blog Post/News/Event.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_albums');
    }
};
