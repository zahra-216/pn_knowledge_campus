<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.4 — belongs to exactly one Faculty, groups
 * Courses. `faculty_id` is ON DELETE RESTRICT at the database level, but
 * since Faculty uses soft deletes, FacultyController::destroy() also
 * blocks at the application layer with a 409 when departments exist
 * (API Design, Section 6.2's documented conflict response) — a soft
 * delete never touches the FK constraint, so the DB-level RESTRICT alone
 * wouldn't catch it.
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule, same as
 * Faculty. Banner attaches via the shared polymorphic `media` table
 * (collection 'banner') — a Department Management hardening addition
 * beyond the base document, mirroring Faculty's own banner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->smallInteger('order')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('faculty_id');
            $table->index('status');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
