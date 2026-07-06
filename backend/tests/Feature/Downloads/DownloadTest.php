<?php

namespace Tests\Feature\Downloads;

use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadTest extends TestCase
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

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/downloads', [
            'title' => 'Undergraduate Prospectus',
            'description' => 'Programme overview.',
        ]);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/downloads/{$id}")->assertForbidden();
    }

    public function test_admissions_can_create_and_edit_but_not_delete(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $response = $this->actingAs($admissions)->postJson('/api/v1/admin/downloads', [
            'title' => 'Application Form',
        ]);
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($admissions)->putJson("/api/v1/admin/downloads/{$id}", ['title' => 'Updated Form'])->assertOk();
        $this->actingAs($admissions)->deleteJson("/api/v1/admin/downloads/{$id}")->assertForbidden();
    }

    public function test_download_can_be_assigned_a_category(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = DownloadCategory::create(['name' => 'Forms', 'slug' => 'forms']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/downloads', [
            'title' => 'Application Form',
            'category_id' => $category->id,
        ]);

        $response->assertCreated();
        $this->assertSame($category->id, $response->json('data.category.id'));
    }

    public function test_a_file_can_be_attached_and_replaced(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $file = UploadedFile::fake()->create('prospectus.pdf', 500, 'application/pdf');

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', ['file' => $file]);
        $upload->assertCreated();
        $mediaId = $upload->json('data.id');

        $create = $this->actingAs($admin)->postJson('/api/v1/admin/downloads', [
            'title' => 'Undergraduate Prospectus',
            'media_id' => $mediaId,
        ]);
        $create->assertCreated();
        $this->assertNotNull($create->json('data.file_url'));

        $downloadId = $create->json('data.id');
        $this->actingAs($admin)->putJson("/api/v1/admin/downloads/{$downloadId}", ['media_id' => null])->assertOk();
        $this->assertNull($this->actingAs($admin)->getJson("/api/v1/admin/downloads/{$downloadId}")->json('data.file_url'));
    }

    public function test_public_endpoint_only_returns_active_downloads_in_order(): void
    {
        Download::create(['title' => 'Second', 'order' => 1, 'is_active' => true]);
        Download::create(['title' => 'Hidden', 'order' => 0, 'is_active' => false]);
        Download::create(['title' => 'First', 'order' => 0, 'is_active' => true]);

        $response = $this->getJson('/api/v1/downloads');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertSame(['First', 'Second'], $titles->all());
    }

    public function test_public_endpoint_can_be_filtered_by_category(): void
    {
        $prospectus = DownloadCategory::create(['name' => 'Prospectus', 'slug' => 'prospectus']);
        $forms = DownloadCategory::create(['name' => 'Forms', 'slug' => 'forms']);

        Download::create(['title' => 'Undergraduate Prospectus', 'category_id' => $prospectus->id, 'is_active' => true]);
        Download::create(['title' => 'Application Form', 'category_id' => $forms->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/downloads?category=forms');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertSame(['Application Form'], $titles->all());
    }
}
