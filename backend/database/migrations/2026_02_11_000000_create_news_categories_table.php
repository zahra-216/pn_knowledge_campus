<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "kept separate from Blog categories
 * since editorial ownership and permission scope differ per the
 * Permission Matrix" (the inverse framing of blog_categories' own
 * docblock). Flat taxonomy, same shape as blog_categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_categories');
    }
};
