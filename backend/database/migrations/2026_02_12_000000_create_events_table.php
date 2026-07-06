<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "Campus events (open days, seminars,
 * graduations)." Media (featured_image, gallery) attaches via the
 * shared polymorphic `media` table; SEO via the shared `seo_meta` table
 * — no columns here for either. The per-event FAQ sub-resource (the
 * shared `faqs` table also serves Events, per Section 4.8) isn't wired
 * up in this milestone — not a requested feature here, same as it
 * wasn't for Blog/News.
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule, same as
 * Course/Blog Post/News. No `registration_url`-backed RSVP/attendee
 * capture table — the doc only specifies a link-out URL field, not an
 * internal registration system.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('venue', 200)->nullable();
            $table->boolean('is_online')->default(false);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->longText('description');
            $table->string('registration_url', 255)->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
