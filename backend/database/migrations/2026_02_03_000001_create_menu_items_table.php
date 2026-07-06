<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.5 — individual navigation entries, nestable
 * (self-referencing parent_id, arbitrary depth), linking either to an
 * internal content record (linkable_type/linkable_id, polymorphic) or a
 * custom URL — exactly one of the two, enforced at the application layer
 * (MenuItemRequest), never both, never neither.
 *
 * Fields beyond the Database Design document (Menu Builder hardening —
 * "mega menu ready" and "visibility rules"):
 *   - description, icon, is_mega_menu — enough per-item richness for a
 *     future public nav to render a multi-column dropdown, not just a
 *     flat list. is_mega_menu only means anything on an item with
 *     children; harmless/ignored on a leaf item.
 *   - starts_at / ends_at — scheduled visibility, the same pattern
 *     hero_slides already uses for "only show within this window".
 *   - visible_on — desktop/mobile/both targeting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->string('custom_url', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('icon', 60)->nullable();
            $table->boolean('is_mega_menu')->default(false);
            $table->boolean('open_in_new_tab')->default(false);
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->enum('visible_on', ['both', 'desktop', 'mobile'])->default('both');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'order']);
            $table->index(['linkable_type', 'linkable_id']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
