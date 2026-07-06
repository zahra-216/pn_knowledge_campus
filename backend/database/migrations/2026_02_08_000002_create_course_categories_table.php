<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not in the Database Design document — "Category" was requested as a
 * Course Management field beyond the base schema. Normalized as its own
 * lookup table, mirroring course_levels/course_modes exactly, rather
 * than a free-text column (consistent with the doc's own stated reason
 * for normalizing Level/Mode).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('slug', 70)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
