<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "structurally identical to
 * [Blog Posts]" (the doc frames it from Blog's side: "Blog... kept as a
 * separate table because News and Blog are separate CMS modules").
 * Media (featured_image, gallery) attaches via the shared polymorphic
 * `media` table; SEO via the shared `seo_meta` table — no columns here
 * for either. Unlike Blog, News doesn't consume the shared `tags` table
 * in this milestone (not a requested feature here — the polymorphic
 * `taggables` pivot already supports adding it later without a schema
 * change) and has no "Related Posts" computation.
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule, same as
 * Blog Post. No `order` column — same reasoning as blog_posts:
 * chronological (published_at), not manually curated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index(['status', 'published_at']);
            $table->index('is_featured');

            // SQLite (the test suite's driver) has no fulltext index
            // support; MySQL (production) does — same guard as
            // courses/blog_posts.
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'excerpt']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
