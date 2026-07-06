<?php

namespace Tests\Feature\Menus;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemTest extends TestCase
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

    public function test_item_requires_exactly_one_of_custom_url_or_linkable(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'header']);

        $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'Home',
        ])->assertUnprocessable()->assertJsonValidationErrors(['custom_url']);

        $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'Home',
            'custom_url' => '/',
            'linkable_type' => 'page',
            'linkable_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['custom_url']);
    }

    public function test_internal_link_is_rejected_for_an_unrecognized_type(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'header']);

        $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'Courses',
            'linkable_type' => 'not-a-real-type',
            'linkable_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['linkable_type']);
    }

    /**
     * Public Website milestone — Faculty/Department/Course/BlogPost/
     * News/Event/GalleryAlbum are now registered in
     * config('menus.linkable_types') alongside Page, so a menu item can
     * link directly to a Course and have the public menu endpoint
     * resolve its real public URL.
     */
    public function test_internal_link_to_a_course_resolves_its_public_url(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'header']);

        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);

        $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'BSc Computer Science',
            'linkable_type' => 'course',
            'linkable_id' => $course->id,
        ])->assertCreated();

        $response = $this->getJson('/api/v1/menus/header');

        $response->assertOk();
        $this->assertSame('/courses/bsc-computer-science', $response->json('data.items.0.url'));
    }

    public function test_can_create_nested_items_and_mark_a_parent_as_mega_menu(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'header']);

        $parent = $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'Academics',
            'custom_url' => '#',
            'is_mega_menu' => true,
        ])->json('data');

        $this->assertTrue($parent['is_mega_menu']);

        $child = $this->actingAs($admin)->postJson("/api/v1/admin/menus/{$menu->id}/items", [
            'label' => 'Courses',
            'custom_url' => '/courses',
            'parent_id' => $parent['id'],
        ])->json('data');

        $this->assertSame($parent['id'], $child['parent_id']);

        $tree = $this->actingAs($admin)->getJson("/api/v1/admin/menus/{$menu->id}/items")->json('data');
        $this->assertCount(1, $tree);
        $this->assertCount(1, $tree[0]['children']);
    }

    public function test_reorder_updates_order_and_nesting_in_one_request(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'header']);

        $a = MenuItem::create(['menu_id' => $menu->id, 'label' => 'A', 'custom_url' => '/a', 'order' => 0]);
        $b = MenuItem::create(['menu_id' => $menu->id, 'label' => 'B', 'custom_url' => '/b', 'order' => 1]);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/menus/{$menu->id}/items/reorder", [
            'items' => [
                ['id' => $b->id, 'parent_id' => null, 'order' => 0],
                ['id' => $a->id, 'parent_id' => $b->id, 'order' => 0],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('menu_items', ['id' => $a->id, 'parent_id' => $b->id, 'order' => 0]);
        $this->assertDatabaseHas('menu_items', ['id' => $b->id, 'parent_id' => null, 'order' => 0]);
    }

    public function test_item_route_is_scoped_to_its_menu(): void
    {
        $admin = $this->superAdmin();
        $header = Menu::create(['name' => 'header']);
        $footer = Menu::create(['name' => 'footer']);

        $footerItem = MenuItem::create(['menu_id' => $footer->id, 'label' => 'Privacy', 'custom_url' => '/privacy']);

        // Requesting the footer's item through the header menu's URL
        // must 404 — scopeBindings() enforces this, not manual checks.
        $this->actingAs($admin)->putJson("/api/v1/admin/menus/{$header->id}/items/{$footerItem->id}", [
            'label' => 'Hijacked',
        ])->assertNotFound();
    }

    public function test_public_endpoint_only_returns_active_and_currently_scheduled_items(): void
    {
        $menu = Menu::create(['name' => 'header']);

        MenuItem::create(['menu_id' => $menu->id, 'label' => 'Visible', 'custom_url' => '/visible', 'is_active' => true]);
        MenuItem::create(['menu_id' => $menu->id, 'label' => 'Inactive', 'custom_url' => '/inactive', 'is_active' => false]);
        MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Future',
            'custom_url' => '/future',
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);
        MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Expired',
            'custom_url' => '/expired',
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/menus/header');

        $response->assertOk();
        $labels = collect($response->json('data.items'))->pluck('label');

        $this->assertSame(['Visible'], $labels->all());
    }

    public function test_public_endpoint_resolves_url_from_custom_url(): void
    {
        $menu = Menu::create(['name' => 'header']);
        MenuItem::create(['menu_id' => $menu->id, 'label' => 'Home', 'custom_url' => '/']);

        $response = $this->getJson('/api/v1/menus/header');

        $response->assertOk();
        $this->assertSame('/', $response->json('data.items.0.url'));
    }

    public function test_unknown_menu_key_404s_on_the_public_endpoint(): void
    {
        $this->getJson('/api/v1/menus/does-not-exist')->assertNotFound();
    }
}
