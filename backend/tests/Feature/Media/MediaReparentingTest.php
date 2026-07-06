<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Tests\TestCase;

/**
 * "Everything should integrate with future modules" — proves the exact
 * mechanism a real content model (Course, Page, ...) will use once it
 * exists: Media::moveKeepingCustomFields($owner, $collection). No CMS
 * page is built here (per this task's own instruction not to) — this
 * test uses a throwaway, test-only HasMedia model backed by a table
 * created and dropped entirely within this test, purely to exercise the
 * re-parenting mechanism the Database Design document already specifies
 * (Section 2.4's polymorphic media pattern) before any real owner model
 * exists.
 *
 * Two real findings from writing this test, both now handled by
 * Media::moveKeepingCustomFields() rather than left as landmines for
 * the next milestone:
 *   1. AppServiceProvider calls Relation::enforceMorphMap(), which
 *      throws ClassMorphViolationException for any model not registered
 *      in that map — every future content model needs its own morph map
 *      entry before it can own media, not just a HasMedia trait.
 *   2. Spatie's move()/copy() only preserve Spatie's own fields — our
 *      three custom columns (alt_text, folder_id, uploaded_by) are
 *      silently dropped on a raw move() unless carried over explicitly.
 */
class MediaReparentingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');

        Schema::create('test_media_owners', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_media_owners');
        parent::tearDown();
    }

    private function fakeOwnerModel(): Model
    {
        return new class extends Model implements HasMedia
        {
            use InteractsWithMedia;

            protected $table = 'test_media_owners';

            public function registerMediaCollections(): void
            {
                $this->addMediaCollection('gallery');
            }
        };
    }

    public function test_media_can_be_moved_from_the_library_singleton_onto_a_real_content_owner(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $upload = $this->actingAs($admin)->postJson('/api/v1/admin/media', [
            'file' => UploadedFile::fake()->image('course-hero.jpg'),
            'alt_text' => 'Course hero image',
        ])->json('data');

        /** @var Media $media */
        $media = Media::findOrFail($upload['id']);
        // Stored as the morph map alias (AppServiceProvider), not the FQCN.
        $this->assertSame('media_library', $media->model_type);

        $owner = $this->fakeOwnerModel();
        $owner->save();

        // The one-line prerequisite described above — without this,
        // move() throws ClassMorphViolationException.
        Relation::morphMap(['test_media_owner' => $owner::class]);

        $newMedia = $media->moveKeepingCustomFields($owner, 'gallery');

        // Spatie's move() works via copy()+delete(), so this is a new
        // row — the old id is gone. Future modules should reference
        // media by (owner, collection), matching the Database Design
        // document's polymorphic attachment pattern, not a raw media id.
        $this->assertNotSame($media->id, $newMedia->id);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);

        $this->assertSame('test_media_owner', $newMedia->model_type);
        $this->assertSame($owner->id, $newMedia->model_id);
        $this->assertSame('gallery', $newMedia->collection_name);

        // The custom columns (folder_id, alt_text, uploaded_by) are
        // explicitly carried over by moveKeepingCustomFields() — a raw
        // Spatie move() would have silently dropped them.
        $this->assertSame('Course hero image', $newMedia->alt_text);

        // Once moved, it belongs to the new owner's collection, not the
        // library-wide browse (which only ever shows what's still
        // unattached in this milestone's scope).
        $this->assertCount(1, $owner->getMedia('gallery'));
    }
}
