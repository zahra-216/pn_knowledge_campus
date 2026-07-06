<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — static/informational pages composed via
 * the Page Builder. `status` uses the global Status Lifecycle enum
 * (Section 2.2: draft/published/scheduled/archived) shared by every
 * content table, not a page-specific subset — published_at doubles as
 * the actual publish time and, while status='scheduled', the trigger
 * time for the FR-37 auto-publish job (see PublishScheduledPages).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('template', 60)->default('default');
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
