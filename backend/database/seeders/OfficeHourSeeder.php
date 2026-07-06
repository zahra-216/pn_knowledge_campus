<?php

namespace Database\Seeders;

use App\Models\OfficeHour;
use Illuminate\Database\Seeder;

/**
 * Seeds the 7 fixed day-of-week rows with a reasonable Mon-Fri
 * 08:30-17:00 / weekends-closed default — all editable afterwards via
 * the Settings > Contact & Social screen. Idempotent (firstOrCreate by
 * day) so re-running seeders never duplicates rows.
 */
class OfficeHourSeeder extends Seeder
{
    public function run(): void
    {
        foreach (OfficeHour::DAYS as $index => $day) {
            $isWeekend = in_array($day, ['saturday', 'sunday'], true);

            OfficeHour::firstOrCreate(
                ['day' => $day],
                [
                    'is_open' => ! $isWeekend,
                    'opens_at' => $isWeekend ? null : '08:30:00',
                    'closes_at' => $isWeekend ? null : '17:00:00',
                    'note' => null,
                    'order' => $index,
                ]
            );
        }
    }
}
