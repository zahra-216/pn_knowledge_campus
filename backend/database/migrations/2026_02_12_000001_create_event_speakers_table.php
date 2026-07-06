<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Not in the Database Design document — "Speakers" was requested as an
 * Events feature beyond the base schema. Normalized as its own child
 * table (event_id FK, cascade on delete since a speaker has no meaning
 * without its event) rather than a JSON column on `events` — this
 * project reserves JSON columns for the two cases the doc explicitly
 * calls out as justified exceptions (page_blocks.data, settings), and a
 * speaker list is structurally identical to CourseCurriculumItem/Faq's
 * child-table pattern. Photo attaches via the shared polymorphic `media`
 * table (collection 'photo'), same as every other single-image field in
 * this project.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('title', 150)->nullable();
            $table->text('bio')->nullable();
            $table->smallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_speakers');
    }
};
