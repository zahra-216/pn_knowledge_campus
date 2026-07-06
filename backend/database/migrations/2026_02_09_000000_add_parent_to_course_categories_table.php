<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course Categories graduate from a flat {name, slug, order} lookup
 * (Milestone 9) into a full entity: this adds self-referencing
 * `parent_id` for a Category > Subcategory tree. Icon/Image are Media
 * Library collections (no column needed) and SEO attaches via the
 * existing polymorphic seo_meta table — both registered on the model/
 * config side, not here. Cascades on delete, same as menu_items/
 * course_curriculum_items: deleting a parent category removes its
 * subcategories (courses under either just fall back to
 * category_id = null via the existing nullOnDelete FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('course_categories')->cascadeOnDelete();
            $table->index(['parent_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('course_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
