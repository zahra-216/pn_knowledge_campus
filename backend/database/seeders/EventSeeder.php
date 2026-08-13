<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $upcoming = $this->createEvent(
            title: 'Open Day 2026',
            venue: 'Main Campus Auditorium',
            startsAt: Carbon::now()->addWeeks(2),
            endsAt: Carbon::now()->addWeeks(2)->addHours(4),
        );

        $upcoming?->speakers()->firstOrCreate(['name' => 'Dr. Amara Silva'], ['title' => 'Dean of Admissions', 'order' => 0]);

        $this->createEvent(
            title: 'Annual Tech Symposium',
            venue: null,
            startsAt: Carbon::now()->addMonth(),
            endsAt: Carbon::now()->addMonth()->addHours(6),
            isOnline: true,
        );

        $this->createEvent(
            title: 'Graduation Ceremony 2025',
            venue: 'Grand Hall',
            startsAt: Carbon::now()->subMonths(2),
            endsAt: Carbon::now()->subMonths(2)->addHours(3),
        );
    }

    private function createEvent(string $title, ?string $venue, Carbon $startsAt, ?Carbon $endsAt = null, bool $isOnline = false): ?Event
    {
        $slug = Str::slug($title);

        if (Event::where('slug', $slug)->exists()) {
            return Event::where('slug', $slug)->first();
        }

        return Event::create([
            'title' => $title,
            'slug' => $slug,
            'venue' => $venue,
            'is_online' => $isOnline,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            // Audit fix (Medium remediation) — this field is a plain
            // <Textarea> in the admin editor (EventDetailsTab.tsx), not a
            // rich-text field; wrapping it in <p> tags here meant every
            // event's description (and its meta description) showed
            // literal, visible "<p>...</p>" tags rather than being parsed.
            'description' => "Details for {$title}.",
            'status' => 'published',
        ]);
    }
}
