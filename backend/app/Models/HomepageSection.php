<?php

namespace App\Models;

use App\Support\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per homepage section (Database Design, Section 4.5) — pure
 * composition state (enabled + order), never content. Always exactly
 * SECTIONS.count() rows, seeded once by HomepageSectionSeeder; the admin
 * Homepage Builder screen edits them in place rather than creating or
 * deleting rows, the same fixed-set-of-rows pattern already used for
 * OfficeHour.
 */
class HomepageSection extends Model
{
    use HasAuditColumns, HasFactory;

    /**
     * Every section key the Homepage Builder can toggle/reorder, per the
     * requested feature list. Content availability differs by key: hero,
     * testimonials, and partners pull from real tables built alongside
     * this migration; featured_courses/faculties/news/events pull from
     * modules that don't exist yet in this codebase (their own later
     * Development Roadmap milestones) and honestly return no items until
     * then; welcome/why_choose_us/cta/footer_widgets pull from
     * config('settings')'s 'homepage' group.
     */
    public const SECTIONS = [
        'hero',
        'welcome',
        'featured_courses',
        'faculties',
        'why_choose_us',
        'statistics',
        'testimonials',
        'partners',
        'latest_news',
        'upcoming_events',
        'cta',
        'footer_widgets',
    ];

    protected $fillable = [
        'section_key',
        'order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
