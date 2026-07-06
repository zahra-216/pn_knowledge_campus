<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Settings module extension (not in the original Database Design
 * document — a client-directed addition). Office Hours is genuinely
 * structured, per-day data (7 fixed rows, each with its own open/closed
 * state and time range) rather than a single scalar value, so it gets
 * its own table instead of being crammed into the `settings` key/value
 * registry — the same reasoning the Database Design document already
 * applies to Branches and Social Links.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_hours', function (Blueprint $table) {
            $table->id();
            $table->string('day', 10)->unique(); // monday .. sunday
            $table->boolean('is_open')->default(true);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->smallInteger('order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_hours');
    }
};
