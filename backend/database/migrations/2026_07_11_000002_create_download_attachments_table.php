<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (High remediation) — the Database Design document's
 * documented reuse pivot ("one catalog file linked from multiple
 * Course/Page sections," FR-25) was never implemented. `download_id`
 * cascades (delete the Download, every attachment referencing it goes
 * too); `attachable_type`/`attachable_id` is a plain polymorphic pair,
 * not a `morphTo` FK, since the "many" side spans multiple unrelated
 * tables (Course, Page, ...) with no single foreign key to constrain
 * against — same pattern as `seo_meta`'s (seoable_type, seoable_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_id')->constrained('downloads')->cascadeOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->timestamps();

            $table->unique(['download_id', 'attachable_type', 'attachable_id'], 'download_attachments_unique');
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_attachments');
    }
};
