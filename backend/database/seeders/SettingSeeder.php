<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds one row per key declared in config/settings.php, with an empty
 * value — Super Admin fills these in via the Settings screen. Idempotent
 * (firstOrCreate by key) so re-running seeders never duplicates rows or
 * clobbers values an admin has already set.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('settings', []) as $group => $keys) {
            foreach ($keys as $key => $meta) {
                Setting::firstOrCreate(
                    ['key' => $key],
                    [
                        'value' => null,
                        'group' => $group,
                        'is_public' => $meta['is_public'],
                    ]
                );
            }
        }
    }
}
