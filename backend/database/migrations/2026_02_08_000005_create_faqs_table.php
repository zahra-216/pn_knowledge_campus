<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.8 — "a single FAQ table serves both the
 * global Site FAQ and per-entity FAQs (Courses, Events) via a nullable
 * polymorphic pair — when faqable_type/faqable_id are both null, the row
 * is a global FAQ." Only the Course-scoped path is wired up in this
 * milestone (Course Management); the global Site FAQ admin module and
 * Events are separate, later Development Roadmap stages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('faqable_type')->nullable();
            $table->unsignedBigInteger('faqable_id')->nullable();
            $table->string('question', 300);
            $table->text('answer');
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['faqable_type', 'faqable_id']);
            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
