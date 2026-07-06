<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.6 — "polymorphic pivot linking tags to
 * News or Blog Posts (extensible to Events/Courses later without a
 * schema change)." Column shape matches Eloquent's morphToMany
 * convention exactly (taggable_id/taggable_type + tag_id), so BlogPost's
 * tags() relation is a plain morphToMany with no custom pivot config.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->string('taggable_type');
            $table->unsignedBigInteger('taggable_id');

            $table->primary(['tag_id', 'taggable_type', 'taggable_id']);
            $table->index(['taggable_type', 'taggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
