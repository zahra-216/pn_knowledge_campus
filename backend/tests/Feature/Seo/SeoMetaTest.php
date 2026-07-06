<?php

namespace Tests\Feature\Seo;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * config('seo.seoable_types') now has real entries (faculty, department,
 * course, course-category, blog, news, event, page — each exercised by
 * its own module's tests, e.g. FacultyTest's "seo can be managed through
 * the generic seo endpoint"). This file only covers the {type}
 * allow-list guard itself: 'gallery-album' is used as a *permanently*
 * unregistered type — the Database Design document explicitly excludes
 * Gallery Albums from the seo_meta consumer list, unlike every other
 * placeholder this test has used before ('news', then 'page'), which
 * kept breaking as each of those milestones shipped.
 */
class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_unregistered_seoable_type_returns_404(): void
    {
        $this->actingAs($this->superAdmin())
            ->getJson('/api/v1/admin/seo/gallery-album/1')
            ->assertNotFound()
            ->assertJsonFragment(['message' => '"gallery-album" is not a recognized SEO-enabled entity type.']);
    }

    public function test_marketing_role_has_seo_edit_but_admissions_does_not(): void
    {
        $marketing = User::factory()->create();
        $marketing->assignRole('Marketing');

        $admissions = User::factory()->create();
        $admissions->assignRole('Admissions');

        // Both hit the same never-registered type — Marketing clears
        // the permission gate and fails at the type-resolution 404;
        // Admissions never gets past the permission gate (403).
        $this->actingAs($marketing)->getJson('/api/v1/admin/seo/gallery-album/1')->assertNotFound();
        $this->actingAs($admissions)->getJson('/api/v1/admin/seo/gallery-album/1')->assertForbidden();
    }
}
