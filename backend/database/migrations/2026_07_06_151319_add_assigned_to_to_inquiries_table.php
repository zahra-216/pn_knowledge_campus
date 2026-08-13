<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (High remediation) — both the SRS ("optional assignment to
 * a staff member") and the Database Design document specify an
 * `assigned_to` column; the `inquiries` migration shipped without one,
 * leaving the admin inbox able to view/change status but never hand an
 * inquiry to a specific staff member.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
