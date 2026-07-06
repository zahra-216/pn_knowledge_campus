<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie Media Library's package-managed table shape (Database Design,
 * Section 2.5 — hand-written to match the package's real schema exactly,
 * the same approach already used for personal_access_tokens and the
 * Spatie Permission tables in Milestone 0), extended with the three
 * custom columns the Database Design document specifies in Section 4.3:
 * folder_id, alt_text, uploaded_by.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->uuid('uuid')->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();

            // Custom additions (Database Design, Section 4.3).
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('alt_text', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('collection_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
