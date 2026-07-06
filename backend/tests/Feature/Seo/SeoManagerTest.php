<?php

namespace Tests\Feature\Seo;

use App\Models\Faculty;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SEO Manager overview screen (SeoMetaController::index/typeIndex) —
 * added once every content module had shipped and
 * config('seo.seoable_types') had its final 8 entries. Covers the
 * "what's missing SEO" summary and the per-type drill-down list; the
 * per-entity GET/PUT show/update behavior itself is SeoMetaTest's job.
 */
class SeoManagerTest extends TestCase
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

    public function test_summary_reports_totals_and_missing_seo_per_type(): void
    {
        $withSeo = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $withSeo->seoMeta()->create(['seo_title' => 'Faculty of Business | PNKC']);
        Faculty::create(['name' => 'Faculty of Law', 'slug' => 'faculty-of-law']);

        $response = $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/seo');

        $response->assertOk();
        $faculties = collect($response->json('data'))->firstWhere('type', 'faculty');

        $this->assertSame(2, $faculties['total']);
        $this->assertSame(1, $faculties['with_seo']);
        $this->assertSame(1, $faculties['missing']);
    }

    public function test_type_drill_down_lists_each_entity_with_seo_status(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        $faculty->seoMeta()->create(['seo_title' => 'Custom Title']);
        Faculty::create(['name' => 'Faculty of Law', 'slug' => 'faculty-of-law']);

        $response = $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/seo/faculty');

        $response->assertOk();
        $rows = collect($response->json('data'));

        $business = $rows->firstWhere('label', 'Faculty of Business');
        $law = $rows->firstWhere('label', 'Faculty of Law');

        $this->assertTrue($business['has_seo']);
        $this->assertSame('Custom Title', $business['seo_title']);
        $this->assertFalse($law['has_seo']);
    }

    public function test_drill_down_can_be_searched_by_label(): void
    {
        Faculty::create(['name' => 'Faculty of Business', 'slug' => 'faculty-of-business']);
        Faculty::create(['name' => 'Faculty of Law', 'slug' => 'faculty-of-law']);

        $response = $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/seo/faculty?search=Law');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_unregistered_type_404s_on_the_drill_down_too(): void
    {
        $this->actingAs($this->superAdmin())->getJson('/api/v1/admin/seo/gallery-album')->assertNotFound();
    }

    public function test_admissions_has_no_access_to_the_seo_manager(): void
    {
        $admissions = User::factory()->create();
        $admissions->assignRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/seo')->assertForbidden();
    }
}
