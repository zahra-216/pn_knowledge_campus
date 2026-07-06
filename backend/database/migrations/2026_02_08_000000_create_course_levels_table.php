<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.3 — lookup table for course levels
 * (Certificate, Diploma, Undergraduate, Postgraduate, etc.), normalized
 * instead of a free-text or hardcoded enum column so Admins can add new
 * levels without a code change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('slug', 70)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_levels');
    }
};
