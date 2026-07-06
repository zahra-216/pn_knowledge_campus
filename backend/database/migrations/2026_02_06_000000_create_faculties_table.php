<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.4 — top of the academic hierarchy (e.g.
 * Faculty of Business, Faculty of Engineering). Image attaches via the
 * shared polymorphic `media` table; the doc specifies a single 'icon'
 * collection, extended here (Faculty Management hardening) with
 * 'banner' and 'gallery' collections plus Dean fields — none of which
 * are in the original document, all explicitly requested for this
 * milestone.
 *
 * `deleted_at` (soft delete) matches Section 2.1's blanket rule for
 * primary content tables ("so accidental deletion is recoverable") —
 * the first model in this codebase to actually use it; Menu/Page/
 * HeroSlide/Testimonial/Partner hard-delete today, a pre-existing gap
 * this migration doesn't retroactively fix on those tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('dean_name', 150)->nullable();
            $table->string('dean_title', 150)->nullable();
            $table->text('dean_message')->nullable();
            $table->smallInteger('order')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculties');
    }
};
