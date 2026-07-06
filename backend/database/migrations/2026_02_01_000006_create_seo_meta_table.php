<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.10 — a single shared table implementing the
 * SRS FR-27 SEO field set for every seoable content type (Faculties,
 * Departments, Courses, Pages, News, Blog Posts, Events), via one
 * polymorphic (seoable_type, seoable_id) pair. Shipped as infrastructure
 * in Milestone 1; the SeoMetaController (upsert-only) lands in this same
 * milestone, but the full SEO Manager screen and audit report are
 * Milestone 7 (Development Roadmap).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('seo_title', 160)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('canonical_url', 255)->nullable();
            $table->string('schema_type', 60)->nullable();
            $table->string('og_title', 160)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('twitter_title', 160)->nullable();
            $table->string('twitter_description', 320)->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
