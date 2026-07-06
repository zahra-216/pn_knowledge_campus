<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — "Pure configuration table controlling
 * which homepage sections are shown and in what order. Content itself is
 * pulled live from the owning module (Hero Slider, Faculties, Courses,
 * News, Testimonials, Partners) — this table stores no content, only
 * composition state." Always exactly 12 rows (HomepageSection::SECTIONS),
 * seeded once; the admin UI toggles/reorders them in place, the same
 * fixed-set-of-rows pattern already used for office_hours.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 60)->unique();
            $table->smallInteger('order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
