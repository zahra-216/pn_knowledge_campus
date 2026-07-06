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

class EventSpeakerTest extends TestCase
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

    private function event(): Event
    {
        return Event::create([
            'title' => 'Open Day 2026', 'slug' => 'open-day-2026',
            'starts_at' => Carbon::now()->addWeek(), 'description' => 'Body',
        ]);
    }

    public function test_speakers_can_be_added_updated_and_removed(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $event = $this->event();

        $store = $this->actingAs($admin)->postJson("/api/v1/admin/events/{$event->id}/speakers", [
            'name' => 'Dr. Amara Silva',
            'title' => 'Dean of Admissions',
        ]);
        $store->assertCreated();
        $this->assertSame('Dr. Amara Silva', $store->json('data.name'));

        $speakerId = $store->json('data.id');

        $update = $this->actingAs($admin)->putJson("/api/v1/admin/events/{$event->id}/speakers/{$speakerId}", [
            'title' => 'Vice Chancellor',
        ]);
        $update->assertOk();
        $this->assertSame('Vice Chancellor', $update->json('data.title'));

        $index = $this->actingAs($admin)->getJson("/api/v1/admin/events/{$event->id}/speakers");
        $index->assertOk();
        $this->assertCount(1, $index->json('data'));

        $this->actingAs($admin)->deleteJson("/api/v1/admin/events/{$event->id}/speakers/{$speakerId}")->assertNoContent();
        $this->assertDatabaseMissing('event_speakers', ['id' => $speakerId]);
    }

    public function test_speaker_photo_attaches_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $event = $this->event();

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('speaker.jpg'),
            'alt_text' => 'Speaker photo',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/events/{$event->id}/speakers", [
            'name' => 'Dr. Amara Silva',
            'photo_media_id' => $upload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.photo_url'));
        $this->assertSame('event_speaker', Media::where('collection_name', 'photo')->first()->model_type);
    }

    public function test_speaker_route_is_scoped_to_its_event(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $eventA = $this->event();
        $eventB = Event::create([
            'title' => 'Tech Symposium', 'slug' => 'tech-symposium',
            'starts_at' => Carbon::now()->addWeek(), 'description' => 'Body',
        ]);

        $speaker = $this->actingAs($admin)->postJson("/api/v1/admin/events/{$eventA->id}/speakers", [
            'name' => 'Dr. Amara Silva',
        ])->json('data');

        $this->actingAs($admin)->putJson("/api/v1/admin/events/{$eventB->id}/speakers/{$speaker['id']}", [
            'name' => 'Hijacked',
        ])->assertNotFound();
    }

    public function test_deleting_an_event_cascades_to_its_speakers(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $event = $this->event();

        $speaker = $this->actingAs($admin)->postJson("/api/v1/admin/events/{$event->id}/speakers", [
            'name' => 'Dr. Amara Silva',
        ])->json('data');

        // Soft delete on Event doesn't cascade (the FK constraint only
        // fires on a real DELETE), but the schema-level cascade is what
        // protects a genuine hard delete/prune later.
        $event->forceDelete();

        $this->assertDatabaseMissing('event_speakers', ['id' => $speaker['id']]);
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');
        $event = $this->event();

        $this->actingAs($admissions)->getJson("/api/v1/admin/events/{$event->id}/speakers")->assertForbidden();
    }
}
