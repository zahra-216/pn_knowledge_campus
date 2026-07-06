<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.3 — self-referencing folder tree used purely
 * for organizing the Media Library UI. Only `created_by` is tracked here
 * (not `updated_by`) — this table's use of audit columns explicitly
 * differs from the Section 2.1 default, per the Database Design's own
 * field list for this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->string('name', 150);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_folders');
    }
};
