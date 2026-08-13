<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Faq;
use App\Models\News;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Audit fix (Medium remediation) — four documented model relationships
 * existed on paper (Database Design / each model's own docblock) but
 * were never implemented: Event had no faqs(), News had no tags(),
 * Course had no inverse testimonials(), and User had no avatar media
 * capability at all. These are plain relation-level tests, not full
 * feature tests — none of the four ship a CRUD/UI flow in this pass
 * (see each model's own docblock for why that's a separate addition).
 */
class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_event_has_a_faqs_relation(): void
    {
        $event = Event::create([
            'title' => 'Open Day', 'slug' => 'open-day', 'starts_at' => now(), 'ends_at' => now()->addHours(2),
            'description' => 'Body',
        ]);
        $event->faqs()->create(['question' => 'Is parking available?', 'answer' => 'Yes, on-site.', 'order' => 0]);

        $this->assertCount(1, $event->fresh()->faqs);
        $this->assertInstanceOf(Faq::class, $event->faqs->first());
    }

    public function test_news_has_a_tags_relation_via_the_shared_taggables_pivot(): void
    {
        $author = User::factory()->create();
        $news = News::create([
            'title' => 'Campus Expansion Announced', 'slug' => 'campus-expansion-announced', 'body' => 'Body',
            'author_id' => $author->id,
        ]);
        $tag = Tag::create(['name' => 'Announcements', 'slug' => 'announcements']);

        $news->tags()->attach($tag->id);

        $this->assertCount(1, $news->fresh()->tags);
        $this->assertTrue($tag->fresh()->news->contains($news));
    }

    public function test_course_has_an_inverse_testimonials_relation(): void
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing', 'slug' => 'faculty-of-computing']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept of CS', 'slug' => 'dept-of-cs']);
        $level = CourseLevel::create(['name' => 'Undergraduate', 'slug' => 'undergraduate']);
        $mode = CourseMode::create(['name' => 'Full-Time', 'slug' => 'full-time']);
        $course = Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => 'BSc Computer Science', 'course_code' => 'CS-001', 'slug' => 'bsc-computer-science',
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview', 'description' => 'Description',
        ]);
        Testimonial::create(['name' => 'Alumni Name', 'content' => 'Great programme.', 'course_id' => $course->id]);

        $this->assertCount(1, $course->fresh()->testimonials);
        $this->assertInstanceOf(Testimonial::class, $course->testimonials->first());
    }

    public function test_user_can_have_an_avatar_via_the_media_library(): void
    {
        $user = User::factory()->create();

        $user->addMedia(UploadedFile::fake()->image('avatar.jpg'))->toMediaCollection('avatar');

        $this->assertNotNull($user->fresh()->getFirstMedia('avatar'));
    }
}
