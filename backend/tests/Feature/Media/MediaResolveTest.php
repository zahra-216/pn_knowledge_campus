<?php

namespace Tests\Feature\Media;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Public Website milestone — GET /api/v1/media/resolve. Settings
 * (logo_media_id, favicon_media_id, ...) and PageBlock.data (hero/image/
 * gallery/video blocks) store bare Media ids with no other public way
 * to resolve them to a URL.
 */
class MediaResolveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    public function test_resolves_multiple_media_ids_without_authentication(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $first = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('logo.jpg'),
            'alt_text' => 'Logo',
        ])->json('data');

        $second = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('favicon.jpg'),
            'alt_text' => 'Favicon',
        ])->json('data');

        $response = $this->getJson("/api/v1/media/resolve?ids={$first['id']},{$second['id']}");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$first['id'], $second['id']], $ids);
        $this->assertNotNull($response->json('data.0.url'));
    }

    public function test_unknown_ids_are_silently_skipped(): void
    {
        $response = $this->getJson('/api/v1/media/resolve?ids=999999');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
