<?php

namespace Tests\Feature\Search;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function course(string $name, string $status = 'published'): Course
    {
        $faculty = Faculty::create(['name' => 'Faculty of Computing '.$name, 'slug' => 'faculty-'.str()->slug($name)]);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'Dept '.$name, 'slug' => 'dept-'.str()->slug($name)]);
        $level = CourseLevel::firstOrCreate(['slug' => 'undergraduate'], ['name' => 'Undergraduate']);
        $mode = CourseMode::firstOrCreate(['slug' => 'full-time'], ['name' => 'Full-Time']);

        return Course::create([
            'faculty_id' => $faculty->id, 'department_id' => $department->id, 'level_id' => $level->id, 'mode_id' => $mode->id,
            'course_name' => $name, 'course_code' => 'C-'.rand(1000, 9999), 'slug' => str()->slug($name),
            'duration_value' => 3, 'duration_unit' => 'year', 'overview' => 'Overview of '.$name, 'description' => 'Description',
            'status' => $status, 'published_at' => $status === 'published' ? now() : null,
        ]);
    }

    public function test_search_finds_matches_across_all_five_types(): void
    {
        $this->course('Computer Science Fundamentals');
        Page::create(['title' => 'Computer Science Facilities', 'slug' => 'cs-facilities', 'status' => 'published', 'published_at' => now()]);

        $author = User::factory()->create();
        BlogPost::create(['title' => 'Computer Science Trends', 'slug' => 'cs-trends', 'body' => 'Body', 'author_id' => $author->id, 'status' => 'published', 'published_at' => now()]);
        News::create(['title' => 'Computer Science Lab Opens', 'slug' => 'cs-lab-opens', 'body' => 'Body', 'author_id' => $author->id, 'status' => 'published', 'published_at' => now()]);
        Event::create(['title' => 'Computer Science Open Day', 'slug' => 'cs-open-day', 'description' => 'Description', 'starts_at' => now()->addDays(5), 'status' => 'published']);

        $response = $this->getJson('/api/v1/search?q=Computer+Science');

        $response->assertOk();
        $results = $response->json('data.results');
        $this->assertSame(1, $results['course']['total']);
        $this->assertSame(1, $results['page']['total']);
        $this->assertSame(1, $results['blog']['total']);
        $this->assertSame(1, $results['news']['total']);
        $this->assertSame(1, $results['event']['total']);
    }

    public function test_search_excludes_draft_and_unpublished_records(): void
    {
        $this->course('Hidden Draft Course', status: 'draft');

        $response = $this->getJson('/api/v1/search?q=Hidden+Draft');

        $response->assertOk();
        $this->assertSame(0, $response->json('data.results.course.total'));
    }

    public function test_search_can_be_filtered_by_type(): void
    {
        $this->course('Filtered Course');
        Page::create(['title' => 'Filtered Page', 'slug' => 'filtered-page', 'status' => 'published', 'published_at' => now()]);

        $response = $this->getJson('/api/v1/search?q=Filtered&type=course');

        $response->assertOk();
        $results = $response->json('data.results');
        $this->assertArrayHasKey('course', $results);
        $this->assertArrayNotHasKey('page', $results);
    }

    public function test_query_must_be_at_least_two_characters(): void
    {
        $this->getJson('/api/v1/search?q=a')->assertUnprocessable()->assertJsonValidationErrors(['q']);
    }

    public function test_autocomplete_returns_a_flat_capped_list(): void
    {
        $this->course('Autocomplete Course One');
        $this->course('Autocomplete Course Two');

        $response = $this->getJson('/api/v1/search/autocomplete?q=Autocomplete');

        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(2, $items);
        $this->assertSame('course', $items[0]['type']);
        $this->assertArrayHasKey('url', $items[0]);
    }
}
