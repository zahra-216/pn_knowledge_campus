<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 17 (FAQ) — groups the global Site FAQ by topic (e.g.
 * "Admissions", "Fees"). Flat taxonomy, same shape as BlogCategory/
 * NewsCategory/PartnerCategory. Course-scoped FAQ (faqs.faqable_type =
 * 'course') deliberately has no category concept — this only applies to
 * the global Site FAQ (faqable_type/id both null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->smallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_categories');
    }
};
