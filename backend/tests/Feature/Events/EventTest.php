<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventTest extends TestCase
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

    private function baseAttributes(): array
    {
        return [
            'title' => 'Open Day 2026',
            'starts_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'description' => '<p>Come visit us.</p>',
        ];
    }

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/events', $this->baseAttributes());
        $response->assertCreated();
        $this->assertSame('open-day-2026', $response->json('data.slug'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/events/{$id}", ['title' => 'Open Day 2026 (Updated)'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/events/{$id}")->assertForbidden();
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/events', $this->baseAttributes());
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/events/{$id}")->assertForbidden();
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/events')->assertForbidden();
        $this->actingAs($admissions)->postJson('/api/v1/admin/events', $this->baseAttributes())->assertForbidden();
    }

    public function test_super_admin_can_delete(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $id = $this->actingAs($admin)->postJson('/api/v1/admin/events', $this->baseAttributes())->json('data.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/events/{$id}")->assertNoContent();
        $this->assertSoftDeleted('events', ['id' => $id]);
    }

    public function test_content_editor_can_set_status_to_published_directly_via_update(): void
    {
        // No separate /publish action exists for Events (unlike News/Blog/Page/Course) —
        // status changes, including to 'published', go through the regular update endpoint.
        $editor = $this->userWithRole('Content Editor');
        $id = $this->actingAs($editor)->postJson('/api/v1/admin/events', $this->baseAttributes())->json('data.id');

        $response = $this->actingAs($editor)->putJson("/api/v1/admin/events/{$id}", ['status' => 'published']);

        $response->assertOk();
        $this->assertSame('published', $response->json('data.status'));
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/events', $this->baseAttributes())->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/events', $this->baseAttributes())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_ends_at_must_not_be_before_starts_at(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/events', [
            ...$this->baseAttributes(),
            'ends_at' => Carbon::now()->toDateTimeString(),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['ends_at']);
    }

    public function test_venue_and_online_flag_can_be_set(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/events', [
            ...$this->baseAttributes(),
            'venue' => 'Main Auditorium',
            'is_online' => false,
        ]);

        $response->assertCreated();
        $this->assertSame('Main Auditorium', $response->json('data.venue'));
        $this->assertFalse($response->json('data.is_online'));
    }

    public function test_featured_image_and_gallery_attach_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $featuredUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('featured.jpg'),
            'alt_text' => 'Featured image',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/events', [
            ...$this->baseAttributes(),
            'featured_image_media_id' => $featuredUpload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.featured_image_url'));
        $this->assertSame('event', Media::where('collection_name', 'featured_image')->first()->model_type);

        $id = $response->json('data.id');

        $galleryUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('gallery-1.jpg'),
            'alt_text' => 'Gallery image',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/events/{$id}/media", [
            'media_ids' => [$galleryUpload['id']],
        ]);
        $attach->assertOk();
        $this->assertCount(1, $attach->json('data.gallery'));

        $mediaId = $attach->json('data.gallery.0.id');
        $this->actingAs($admin)->deleteJson("/api/v1/admin/events/{$id}/media/{$mediaId}")->assertNoContent();
    }

    public function test_public_index_defaults_to_upcoming_and_supports_past_filter(): void
    {
        Event::create([
            'title' => 'Future Event', 'slug' => 'future-event', 'starts_at' => Carbon::now()->addWeek(),
            'description' => 'Body', 'status' => 'published',
        ]);
        Event::create([
            'title' => 'Past Event', 'slug' => 'past-event', 'starts_at' => Carbon::now()->subWeek(),
            'description' => 'Body', 'status' => 'published',
        ]);

        $upcoming = $this->getJson('/api/v1/events');
        $upcoming->assertOk();
        $this->assertSame(['Future Event'], collect($upcoming->json('data'))->pluck('title')->all());

        $past = $this->getJson('/api/v1/events?filter[past]=1');
        $past->assertOk();
        $this->assertSame(['Past Event'], collect($past->json('data'))->pluck('title')->all());
    }

    public function test_public_show_only_returns_published_events(): void
    {
        Event::create([
            'title' => 'Draft Event', 'slug' => 'draft-event', 'starts_at' => Carbon::now()->addWeek(),
            'description' => 'Body', 'status' => 'draft',
        ]);

        $this->getJson('/api/v1/events/draft-event')->assertNotFound();
    }

    public function test_seo_fields_can_be_set_inline_on_create(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/events', [
            ...$this->baseAttributes(),
            'seo' => ['seo_title' => 'Custom SEO Title'],
        ]);

        $response->assertCreated();
        $id = $response->json('data.id');

        $seo = $this->actingAs($admin)->getJson("/api/v1/admin/seo/event/{$id}");
        $seo->assertOk();
        $this->assertSame('Custom SEO Title', $seo->json('data.seo_title'));
    }

    public function test_a_scheduled_event_in_the_past_is_flipped_to_published_by_the_command(): void
    {
        $event = Event::create([
            'title' => 'Open Day 2026', 'slug' => 'open-day-2026', 'starts_at' => Carbon::now()->addWeek(),
            'description' => 'Body', 'status' => 'scheduled', 'published_at' => Carbon::now()->subMinute(),
        ]);

        $this->artisan('events:publish-scheduled');

        $this->assertSame('published', $event->fresh()->status);
    }

    public function test_a_scheduled_event_in_the_future_is_not_flipped(): void
    {
        $event = Event::create([
            'title' => 'Open Day 2026', 'slug' => 'open-day-2026', 'starts_at' => Carbon::now()->addWeek(),
            'description' => 'Body', 'status' => 'scheduled', 'published_at' => Carbon::now()->addDay(),
        ]);

        $this->artisan('events:publish-scheduled');

        $this->assertSame('scheduled', $event->fresh()->status);
    }
}
