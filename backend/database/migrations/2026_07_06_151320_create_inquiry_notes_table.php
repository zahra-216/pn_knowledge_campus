<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (High remediation) — the Database Design document's
 * documented `inquiry_notes` table (staff follow-up notes on an
 * inquiry) was never implemented at all. `inquiry_id` cascades (delete
 * the inquiry, its notes go with it); `user_id` restricts (a staff
 * member can't be deleted while their notes still reference them —
 * same convention as News/BlogPost's `author_id`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index('inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_notes');
    }
};
