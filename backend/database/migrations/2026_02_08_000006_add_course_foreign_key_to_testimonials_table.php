<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * testimonials.course_id was created without an FK constraint (see that
 * migration's docblock) since the courses table didn't exist yet. Course
 * Management adds it now, per Database Design, Section 4.5: "Referenced
 * by testimonials.course_id (nullable)", ON DELETE SET NULL — deleting a
 * course un-links its testimonials rather than deleting them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });
    }
};
