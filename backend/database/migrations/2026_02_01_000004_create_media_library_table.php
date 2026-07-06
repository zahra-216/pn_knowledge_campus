<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Not part of the Database Design document's table list — added to bridge
 * a real gap in it. Spatie Media Library requires every uploaded file to
 * belong to a concrete model via a polymorphic (model_type, model_id)
 * pair, but Milestone 1 ships the Media Library before any real content
 * model (Course, Page, News, ...) exists to own an upload. This is a
 * single-row singleton that owns every asset until a later milestone
 * re-parents it onto a real content record via Spatie's $media->move()
 * (e.g. "Attach an existing Media Library item to this course's gallery",
 * API Design Section 8.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Exactly one row, ever — every M1 upload attaches to id=1 until
        // a later milestone's content model claims it.
        Schema::table('media_library', function () {
            DB::table('media_library')->insert([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_library');
    }
};
