<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 17 (FAQ) — optional category for the global Site FAQ.
 * nullOnDelete so removing a category just leaves its FAQs
 * uncategorized, same reasoning as BlogPost/News/Partner's category_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('faq_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
