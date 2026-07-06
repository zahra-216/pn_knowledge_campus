<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Design, Section 4.2 — a single key/value registry for global,
 * heterogeneous configuration (campus details, SMTP, social, logo/favicon,
 * footer, analytics). group/is_public are fixed per key by config/settings.php
 * at seed time; the bulk-update endpoint (SettingController::update) only
 * ever writes the `value` column, so a client can never flip a secret key
 * (e.g. smtp_password) to is_public=true via the settings API.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->text('value')->nullable();
            $table->string('group', 60)->default('general');
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
