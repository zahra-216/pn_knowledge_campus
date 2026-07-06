<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.3 — lookup table for delivery mode
 * (Full-Time, Part-Time, Online, Blended), normalized for the same
 * reason as course_levels.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('slug', 70)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modes');
    }
};
