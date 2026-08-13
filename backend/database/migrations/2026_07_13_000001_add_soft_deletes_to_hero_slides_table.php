<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (Medium remediation) — Database Design, Section 2.1's
 * blanket soft-delete rule applies to every content table; HeroSlide,
 * Testimonial, Partner, and Menu were the remaining tables that shipped
 * without it (see 2026_07_12_000001's equivalent fix for Pages).
 * Deleting a hero slide was previously unrecoverable outside a full
 * database restore.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
