<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "kept separate from News categories
 * since editorial ownership and permission scope differ per the
 * Permission Matrix." Flat taxonomy (no hierarchy/media/SEO — unlike
 * Course Category, Blog's own feature list only asks for "Categories",
 * not nested subcategories).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};
