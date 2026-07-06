<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 18 (Downloads) — groups the Downloads catalog by document
 * type (e.g. "Prospectus", "Forms", "Brochures"). Flat taxonomy, same
 * shape as BlogCategory/NewsCategory/PartnerCategory/FaqCategory. This
 * is a brand-new cross-entity catalog, distinct from Course's own
 * `downloads` media collection (course-scoped brochures), which is
 * untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_categories');
    }
};
