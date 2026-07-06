<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.3 — structured, normalized curriculum
 * breakdown for a course (module → lesson nesting) instead of a JSON
 * blob, explicitly called out as the Phase 2 LMS attachment point.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_curriculum_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('course_curriculum_items')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('duration', 60)->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();

            $table->index('course_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_curriculum_items');
    }
};
