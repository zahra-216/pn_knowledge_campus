<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 24 (Dashboard Analytics) — a lightweight, first-party page
 * view log, not a full analytics platform. The public SPA pings
 * POST /api/v1/analytics/pageview on every route change with the path
 * and a random `visitor_id` it generates once and keeps in
 * localStorage (no cookies, no PII, no session — see
 * frontend/src/hooks/usePageViewTracking.ts). `visitor_id` lets the
 * Dashboard tell "unique visitors per day" (COUNT DISTINCT visitor_id)
 * apart from "page views per day" (COUNT *) without needing real
 * accounts or a third-party analytics service — config('settings')'s
 * existing `ga_tracking_id`/`gtm_container_id` remain available if a
 * real GA/GTM integration is wired in later, but this table is what the
 * in-app charts (Visitors, Page Views) are built from right now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->string('visitor_id', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
