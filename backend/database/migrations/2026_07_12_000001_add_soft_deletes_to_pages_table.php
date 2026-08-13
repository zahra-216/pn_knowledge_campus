<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (Medium remediation) — Database Design, Section 2.1's
 * blanket soft-delete rule applies to every content table; Pages was
 * the one table that shipped without it (see every other content
 * model's own `deleted_at` column, added at creation time). Deleting a
 * Page — About, Admissions, a legal page — was previously unrecoverable
 * outside a full database restore.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
