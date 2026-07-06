<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.10 — preserves SEO equity when a slug
 * changes. Table only in Milestone 1 (per the Development Roadmap, the
 * UrlRedirectController and its admin screen ship in Milestone 7); no
 * model beyond a plain Eloquent model is needed yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 255)->unique();
            $table->string('to_path', 255);
            $table->smallInteger('redirect_type')->default(301);
            $table->unsignedInteger('hit_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
