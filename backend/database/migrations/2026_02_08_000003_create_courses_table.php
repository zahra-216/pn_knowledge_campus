<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.3 — "the most structurally important table
 * in Phase 1... the anchor Phase 2 (LMS) will attach to." Implements the
 * full field set the SRS specifies, plus two client-requested additions
 * beyond the document: `category_id` (nullable — "Category" has no
 * lookup table in the base doc, added mirroring level/mode) and
 * `discount_price` ("Discount" isn't in the base doc either).
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule, same as
 * Faculty/Department. Media attaches via the shared polymorphic `media`
 * table (collections 'featured_image', 'gallery', 'downloads') — no
 * columns for those here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('level_id')->constrained('course_levels')->restrictOnDelete();
            $table->foreignId('mode_id')->constrained('course_modes')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->string('course_name', 200);
            $table->string('course_code', 30)->unique();
            $table->string('slug', 220)->unique();
            $table->smallInteger('duration_value');
            $table->enum('duration_unit', ['day', 'week', 'month', 'year']);
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->char('price_currency', 3)->default('LKR');
            $table->string('overview', 500);
            $table->longText('description');
            $table->longText('entry_requirements')->nullable();
            $table->longText('learning_outcomes')->nullable();
            $table->longText('career_opportunities')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->smallInteger('order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('faculty_id');
            $table->index('department_id');
            $table->index('level_id');
            $table->index('mode_id');
            $table->index(['faculty_id', 'department_id', 'status']);
            $table->index('is_featured');

            // SQLite (used for the test suite — phpunit.xml) has no
            // fulltext index support at all; MySQL (production) does.
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['course_name', 'overview']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
