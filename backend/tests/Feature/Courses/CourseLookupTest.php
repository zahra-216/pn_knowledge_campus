<?php

namespace Tests\Feature\Courses;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Covers the baseline CRUD/permission/uniqueness/ordering behavior
 * common to all three lookup endpoints (course-levels, course-modes,
 * course-categories) via a data provider. course-levels/course-modes
 * still share CourseLookupController; course-categories has since grown
 * its own dedicated controller (icon/image media, a parent/child tree,
 * SEO — see CourseCategoryTest) but keeps the same {name, slug, order}
 * request/response baseline, so it stays in this shared regression
 * suite too.
 */
class CourseLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public static function endpoints(): array
    {
        return [
            'course-levels' => ['course-levels'],
            'course-modes' => ['course-modes'],
            'course-categories' => ['course-categories'],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_content_editor_can_create_and_edit_but_not_delete(string $endpoint): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'Diploma']);
        $response->assertCreated();
        $this->assertSame('diploma', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/{$endpoint}/{$id}", ['name' => 'Advanced Diploma'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/{$endpoint}/{$id}")->assertForbidden();
    }

    #[DataProvider('endpoints')]
    public function test_marketing_has_view_only_access(string $endpoint): void
    {
        $marketing = $this->userWithRole('Marketing');

        $this->actingAs($marketing)->getJson("/api/v1/admin/{$endpoint}")->assertOk();
        $this->actingAs($marketing)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'Diploma'])->assertForbidden();
    }

    #[DataProvider('endpoints')]
    public function test_name_and_slug_must_be_unique(string $endpoint): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'Diploma'])->assertCreated();
        $this->actingAs($admin)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'Diploma'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[DataProvider('endpoints')]
    public function test_public_index_lists_in_order(string $endpoint): void
    {
        $admin = $this->userWithRole('Super Admin');
        $this->actingAs($admin)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'Second', 'order' => 1]);
        $this->actingAs($admin)->postJson("/api/v1/admin/{$endpoint}", ['name' => 'First', 'order' => 0]);

        $response = $this->getJson("/api/v1/{$endpoint}");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertSame(['First', 'Second'], $names->all());
    }
}
