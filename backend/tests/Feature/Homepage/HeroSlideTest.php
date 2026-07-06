<?php

namespace Tests\Feature\Homepage;

use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroSlideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/hero-slides', ['title' => 'Welcome']);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->putJson("/api/v1/admin/hero-slides/{$id}", ['title' => 'Updated'])->assertOk();
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/hero-slides/{$id}")->assertForbidden();
    }

    public function test_content_editor_has_no_access_to_hero_slider(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor)->getJson('/api/v1/admin/hero-slides')->assertForbidden();
    }

    public function test_administrator_can_delete(): void
    {
        $admin = $this->userWithRole('Administrator');
        $slide = HeroSlide::create(['title' => 'Slide']);

        $this->actingAs($admin)->deleteJson("/api/v1/admin/hero-slides/{$slide->id}")->assertNoContent();
    }

    public function test_attaching_an_existing_media_item_moves_it_onto_the_slide(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('slide.jpg'),
            'alt_text' => 'Hero slide background',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/hero-slides', [
            'title' => 'With Image',
            'media_id' => $upload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.image_url'));

        // Spatie's move() creates a new media row — the original upload's
        // id is gone once re-parented (see MediaReparentingTest).
        $this->assertDatabaseMissing('media', ['id' => $upload['id']]);
        $this->assertSame('hero_slide', Media::where('collection_name', 'slide_image')->first()->model_type);
    }

    public function test_public_endpoint_only_returns_active_and_currently_scheduled_slides(): void
    {
        HeroSlide::create(['title' => 'Visible', 'is_active' => true]);
        HeroSlide::create(['title' => 'Inactive', 'is_active' => false]);
        HeroSlide::create(['title' => 'Future', 'is_active' => true, 'starts_at' => now()->addDay()]);

        $response = $this->getJson('/api/v1/hero-slides');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertSame(['Visible'], $titles->all());
    }
}
