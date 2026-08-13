<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (High remediation) — FR-06 ("Newsletter/Downloads capture
 * ... prospectus download gated by a short form") and the Database
 * Design document both describe these two columns; neither existed,
 * so a Download could never actually be gated and downloads were never
 * counted. `requires_inquiry` is per-row (most downloads stay
 * ungated — "optionally gated", per the SRS) — see
 * DownloadController::requestDownload()'s docblock for the capture
 * flow this enables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->boolean('requires_inquiry')->default(false)->after('is_active');
            $table->unsignedInteger('download_count')->default(0)->after('requires_inquiry');
        });
    }

    public function down(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropColumn(['requires_inquiry', 'download_count']);
        });
    }
};
