<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 16 (Partners) — links each partner to an optional
 * PartnerCategory. nullOnDelete so removing a category just leaves its
 * partners uncategorized, same reasoning as BlogPost/News's category_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('partner_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
