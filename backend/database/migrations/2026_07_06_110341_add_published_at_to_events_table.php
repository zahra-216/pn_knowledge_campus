<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit fix (Phase 1 Critical/High remediation) — Event's own model
 * docblock previously documented "no published_at column... no
 * PublishScheduledEvents cron job exists" as a deliberate exclusion,
 * reasoning that `starts_at` already represents real-world timing. But
 * the editor UI already lets staff pick status='scheduled' for an
 * Event with nothing to actually schedule against, and the SRS's FR-37
 * makes no per-module exception. This column is "when should this
 * event's LISTING go live" — independent of `starts_at` (when the event
 * itself happens) — mirroring Page/BlogPost/News/Course exactly, so
 * PublishScheduledEvents has the same field to check as its siblings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
