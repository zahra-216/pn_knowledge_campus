<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — student/alumni testimonials, optionally
 * tied to a specific Course. Photo attaches via the shared polymorphic
 * `media` table (collection 'photo'). `course_id` has no FK constraint
 * yet — the courses table doesn't exist in this codebase (a later
 * Development Roadmap milestone, "Academic Structure"); it's a plain
 * nullable/indexed column until that migration ships and can add the
 * constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('role_title', 150)->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->text('content');
            $table->tinyInteger('rating')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('course_id');
            $table->index(['is_featured', 'order']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
