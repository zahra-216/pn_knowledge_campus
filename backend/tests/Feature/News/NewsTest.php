<?php

namespace Tests\Feature\News;

use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsTest extends TestCase
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
            'title' => 'Campus Wins National Innovation Award',
            'body' => '<p>Body content.</p>',
            'excerpt' => 'A short excerpt.',
        ];
    }

    public function test_content_editor_can_create_and_edit_but_not_delete(): void
    {
        $editor = $this->userWithRole('Content Editor');

        $response = $this->actingAs($editor)->postJson('/api/v1/admin/news', $this->baseAttributes());
        $response->assertCreated();
        $this->assertSame('campus-wins-national-innovation-award', $response->json('data.slug'));
        $this->assertSame($editor->id, $response->json('data.author.id'));

        $id = $response->json('data.id');
        $this->actingAs($editor)->putJson("/api/v1/admin/news/{$id}", ['title' => 'Updated Title'])->assertOk();
        $this->actingAs($editor)->deleteJson("/api/v1/admin/news/{$id}")->assertForbidden();
    }

    public function test_marketing_can_create_and_edit_but_not_delete(): void
    {
        $marketing = $this->userWithRole('Marketing');

        $response = $this->actingAs($marketing)->postJson('/api/v1/admin/news', $this->baseAttributes());
        $response->assertCreated();

        $id = $response->json('data.id');
        $this->actingAs($marketing)->deleteJson("/api/v1/admin/news/{$id}")->assertForbidden();
    }

    public function test_admissions_has_no_access(): void
    {
        $admissions = $this->userWithRole('Admissions');

        $this->actingAs($admissions)->getJson('/api/v1/admin/news')->assertForbidden();
        $this->actingAs($admissions)->postJson('/api/v1/admin/news', $this->baseAttributes())->assertForbidden();
    }

    public function test_super_admin_can_delete(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $id = $this->actingAs($admin)->postJson('/api/v1/admin/news', $this->baseAttributes())->json('data.id');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/news/{$id}")->assertNoContent();
        $this->assertSoftDeleted('news', ['id' => $id]);
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)->postJson('/api/v1/admin/news', $this->baseAttributes())->assertCreated();
        $this->actingAs($admin)->postJson('/api/v1/admin/news', $this->baseAttributes())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_category_and_author_can_be_set(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = NewsCategory::create(['name' => 'Announcements', 'slug' => 'announcements']);
        $author = User::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/news', [
            ...$this->baseAttributes(),
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $response->assertCreated();
        $this->assertSame($category->id, $response->json('data.category.id'));
        $this->assertSame($author->id, $response->json('data.author.id'));
    }

    public function test_featured_image_and_gallery_attach_via_media_move(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $featuredUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('featured.jpg'),
            'alt_text' => 'Featured image',
        ])->json('data');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/news', [
            ...$this->baseAttributes(),
            'featured_image_media_id' => $featuredUpload['id'],
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.featured_image_url'));
        $this->assertSame('news', Media::where('collection_name', 'featured_image')->first()->model_type);

        $id = $response->json('data.id');

        $galleryUpload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('gallery-1.jpg'),
            'alt_text' => 'Gallery image',
        ])->json('data');

        $attach = $this->actingAs($admin)->postJson("/api/v1/admin/news/{$id}/media", [
            'media_ids' => [$galleryUpload['id']],
        ]);
        $attach->assertOk();
        $this->assertCount(1, $attach->json('data.gallery'));

        $mediaId = $attach->json('data.gallery.0.id');
        $this->actingAs($admin)->deleteJson("/api/v1/admin/news/{$id}/media/{$mediaId}")->assertNoContent();
    }

    public function test_publish_sets_status_and_published_at(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $id = $this->actingAs($admin)->postJson('/api/v1/admin/news', $this->baseAttributes())->json('data.id');

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/news/{$id}/publish");

        $response->assertOk();
        $this->assertSame('published', $response->json('data.status'));
        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_content_editor_cannot_publish(): void
    {
        $editor = $this->userWithRole('Content Editor');
        $id = $this->actingAs($editor)->postJson('/api/v1/admin/news', $this->baseAttributes())->json('data.id');

        $this->actingAs($editor)->patchJson("/api/v1/admin/news/{$id}/publish")->assertForbidden();
    }

    public function test_a_scheduled_article_in_the_past_is_publicly_visible_and_the_command_flips_it(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $article = News::create([
            'title' => 'Scheduled Article',
            'slug' => 'scheduled-article',
            'body' => 'Body',
            'author_id' => $admin->id,
            'status' => 'scheduled',
            'published_at' => Carbon::now()->subMinute(),
        ]);

        $this->getJson('/api/v1/news/scheduled-article')->assertOk();

        $this->artisan('news:publish-scheduled');

        $this->assertSame('published', $article->fresh()->status);
    }

    public function test_a_scheduled_article_in_the_future_is_not_publicly_visible(): void
    {
        $admin = $this->userWithRole('Super Admin');
        News::create([
            'title' => 'Future Article',
            'slug' => 'future-article',
            'body' => 'Body',
            'author_id' => $admin->id,
            'status' => 'scheduled',
            'published_at' => Carbon::now()->addDay(),
        ]);

        $this->getJson('/api/v1/news/future-article')->assertNotFound();
    }

    public function test_public_show_increments_views_count(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $article = News::create([
            'title' => 'Main Article', 'slug' => 'main-article', 'body' => 'Body',
            'author_id' => $admin->id, 'status' => 'published', 'published_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/news/main-article');

        $response->assertOk();
        $this->assertSame(1, $response->json('data.views_count'));
        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_public_index_filters_by_category_and_search(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $category = NewsCategory::create(['name' => 'Announcements', 'slug' => 'announcements']);

        News::create([
            'category_id' => $category->id, 'title' => 'Orientation Week Kickoff', 'slug' => 'orientation-week-kickoff', 'body' => 'Body',
            'author_id' => $admin->id, 'status' => 'published', 'published_at' => Carbon::now()->subDay(),
        ]);

        News::create([
            'title' => 'Unrelated Draft', 'slug' => 'unrelated-draft', 'body' => 'Body',
            'author_id' => $admin->id, 'status' => 'draft',
        ]);

        $byCategory = $this->getJson('/api/v1/news?filter[category]=announcements');
        $byCategory->assertOk();
        $this->assertCount(1, $byCategory->json('data'));

        $bySearch = $this->getJson('/api/v1/news?search=Orientation');
        $bySearch->assertOk();
        $this->assertCount(1, $bySearch->json('data'));
    }

    public function test_seo_fields_can_be_set_inline_on_create(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/news', [
            ...$this->baseAttributes(),
            'seo' => ['seo_title' => 'Custom SEO Title'],
        ]);

        $response->assertCreated();
        $id = $response->json('data.id');

        $seo = $this->actingAs($admin)->getJson("/api/v1/admin/seo/news/{$id}");
        $seo->assertOk();
        $this->assertSame('Custom SEO Title', $seo->json('data.seo_title'));
    }
}
