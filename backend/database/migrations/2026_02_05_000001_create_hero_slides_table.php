<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — homepage/page banner slides with
 * scheduling. Image attaches via the shared polymorphic `media` table
 * (collection 'slide_image'), per Section 2.4's reuse pattern — not a
 * media_id column here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('subtitle', 300)->nullable();
            $table->string('cta_text', 60)->nullable();
            $table->string('cta_url', 255)->nullable();
            $table->smallInteger('order')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'order']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
