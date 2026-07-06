<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — ordered content blocks composing a
 * page. The second deliberate JSON exception in the schema (Section
 * 6.2): `block_type` determines a different `data` shape per block (a
 * hero needs heading/media/CTA; an FAQ needs a question/answer array).
 * Normalizing each of the 11 block types into its own table would need
 * a polymorphic union just to render a page — a typed, per-type
 * Form-Request-validated JSON payload is the documented pragmatic
 * choice. Audit columns beyond the doc's field list, matching every
 * other child content row in this codebase (see menu_items).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('block_type', 60);
            $table->json('data');
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
