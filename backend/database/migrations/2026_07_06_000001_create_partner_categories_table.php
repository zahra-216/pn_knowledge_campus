<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 16 (Partners) — groups partner/accreditation logos by type
 * (e.g. "Academic Partner", "Industry Partner"). Flat taxonomy, same
 * shape as BlogCategory/NewsCategory (no hierarchy/media/SEO — Partners'
 * own feature list only asks for "Partner Category", not subcategories).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_categories');
    }
};
