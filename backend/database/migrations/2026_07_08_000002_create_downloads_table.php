<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 18 (Downloads) — the standalone Downloads catalog (site
 * Prospectus, Application Forms, Brochures, and other public PDFs), one
 * file per row via Spatie's polymorphic 'file' media collection. Not
 * the same thing as Course's own `downloads` media collection (course
 * brochures scoped to a single course) — this is a cross-entity,
 * independently browsable library.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('download_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
