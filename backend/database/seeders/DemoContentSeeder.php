<?php

namespace Database\Seeders;

use App\Support\PublicCache;
use Database\Seeders\Demo\DemoAcademicSeeder;
use Database\Seeders\Demo\DemoHomepageSeeder;
use Database\Seeders\Demo\DemoPagesSeeder;
use Database\Seeders\Demo\DemoPublishingSeeder;
use Database\Seeders\Demo\DemoSettingsSeeder;
use Illuminate\Database\Seeder;

/**
 * Content Population pass — realistic, internally-consistent demo
 * content for evaluating the public website, layered on top of the
 * baseline rows DatabaseSeeder's own chain already creates (which ship
 * deliberately image-less and minimal — see e.g. FacultySeeder's
 * docblock). Deliberately NOT called from DatabaseSeeder::run(): this
 * is demo content, not the application's required baseline, and every
 * environment (fresh install, CI, an admin's already-customized dev DB)
 * should opt into it explicitly:
 *
 *     php artisan db:seed --class=DemoContentSeeder
 *
 * Every step below is idempotent and additive-only:
 *   - New rows use `firstOrCreate` keyed on the same natural key
 *     (slug/code/title) the existing seeders already use.
 *   - Every media attachment is skipped if that collection already has
 *     something in it (see Demo\Concerns\SeedsMedia::attachImage()) —
 *     an admin's real upload, or a previous run of this same seeder,
 *     is never overwritten.
 *   - Every Setting value is only written if currently null/empty.
 *
 * Re-running this command at any time is safe and will only fill in
 * whatever is still missing; it will never duplicate content or
 * clobber a real edit. See backend/DEPLOYMENT.md / the project's demo
 * content report for how to fully reset and reseed from scratch.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoSettingsSeeder::class,
            DemoAcademicSeeder::class,
            DemoPublishingSeeder::class,
            DemoHomepageSeeder::class,
            DemoPagesSeeder::class,
        ]);

        // Admin writes to Settings/Homepage-adjacent models normally
        // invalidate these explicitly (see PublicCache's docblock) —
        // this seeder writes via Eloquent directly, so it must do the
        // same, or the public site would show stale/cached data for up
        // to 5 minutes after seeding.
        PublicCache::forgetSettings();
        PublicCache::forgetHomepage();
        PublicCache::forgetSocialLinks();
        PublicCache::forgetOfficeHours();

        $this->command?->info('Demo content seeding complete.');
    }
}
