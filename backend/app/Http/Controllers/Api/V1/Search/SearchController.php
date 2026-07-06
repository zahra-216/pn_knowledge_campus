<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Milestone 21 (Global Search) — public, unauthenticated. Each of the
 * five searchable modules (Courses/Pages/Blog/News/Events) already has
 * its own `?search=` filter on its own public list endpoint (see
 * CourseController::publicIndex() etc.) — this is deliberately not a
 * reimplementation of those, but a thin aggregator on top, since a real
 * cross-type UNION query with proper pagination per type would be far
 * more complex than this project needs. `index()` returns a handful of
 * top matches per type (grouped, with a total count) rather than one
 * flat paginated list; the Global Search results page links out to each
 * module's own already-paginated `?search=` listing for "view all N".
 */
class SearchController extends Controller
{
    /** Matches every module's own list-endpoint default; keeps a search results page from ever showing an overwhelming wall of results per type. */
    private const RESULTS_PER_TYPE = 6;

    private const AUTOCOMPLETE_LIMIT = 8;

    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:150']]);

        $term = $request->string('q')->trim()->value();
        $types = $this->requestedTypes($request);

        $results = collect([
            'course' => $this->searchCourses($term, self::RESULTS_PER_TYPE),
            'page' => $this->searchPages($term, self::RESULTS_PER_TYPE),
            'blog' => $this->searchBlog($term, self::RESULTS_PER_TYPE),
            'news' => $this->searchNews($term, self::RESULTS_PER_TYPE),
            'event' => $this->searchEvents($term, self::RESULTS_PER_TYPE),
        ])->only($types);

        return ApiResponse::success([
            'query' => $term,
            'results' => $results->map(fn ($result) => [
                'items' => $result['items'],
                'total' => $result['total'],
            ])->all(),
        ]);
    }

    /**
     * GET /api/v1/search/autocomplete — a fast, small, flat list (not
     * grouped) for a live-typing header dropdown. Pulls the same five
     * types but caps the combined total rather than per type, so the
     * dropdown never shows more than a screenful.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:150']]);

        $term = $request->string('q')->trim()->value();
        $perType = 3;

        $items = collect([
            ...$this->searchCourses($term, $perType)['items'],
            ...$this->searchPages($term, $perType)['items'],
            ...$this->searchBlog($term, $perType)['items'],
            ...$this->searchNews($term, $perType)['items'],
            ...$this->searchEvents($term, $perType)['items'],
        ])->take(self::AUTOCOMPLETE_LIMIT)->values();

        return ApiResponse::success(['query' => $term, 'items' => $items]);
    }

    /** @return array{items: array<int, array<string, mixed>>, total: int} */
    private function searchCourses(string $term, int $limit): array
    {
        $query = Course::published()->where(fn (Builder $q) => $q
            ->where('course_name', 'like', "%{$term}%")
            ->orWhere('overview', 'like', "%{$term}%"));

        return [
            'items' => $query->clone()->orderBy('order')->limit($limit)->get()
                ->map(fn (Course $c) => $this->normalize('course', $c->course_name, $c->overview, "/courses/{$c->slug}", $c))
                ->all(),
            'total' => $query->count(),
        ];
    }

    private function searchPages(string $term, int $limit): array
    {
        $query = Page::publiclyVisible()->where('title', 'like', "%{$term}%");

        return [
            'items' => $query->clone()->orderBy('title')->limit($limit)->get()
                ->map(fn (Page $p) => $this->normalize('page', $p->title, null, "/{$p->slug}", null))
                ->all(),
            'total' => $query->count(),
        ];
    }

    private function searchBlog(string $term, int $limit): array
    {
        $query = BlogPost::published()->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%"));

        return [
            'items' => $query->clone()->orderByDesc('published_at')->limit($limit)->get()
                ->map(fn (BlogPost $p) => $this->normalize('blog', $p->title, $p->excerpt, "/blog/{$p->slug}", $p))
                ->all(),
            'total' => $query->count(),
        ];
    }

    private function searchNews(string $term, int $limit): array
    {
        $query = News::published()->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%"));

        return [
            'items' => $query->clone()->orderByDesc('published_at')->limit($limit)->get()
                ->map(fn (News $n) => $this->normalize('news', $n->title, $n->excerpt, "/news/{$n->slug}", $n))
                ->all(),
            'total' => $query->count(),
        ];
    }

    private function searchEvents(string $term, int $limit): array
    {
        $query = Event::published()->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%"));

        return [
            'items' => $query->clone()->orderBy('starts_at')->limit($limit)->get()
                ->map(fn (Event $e) => $this->normalize('event', $e->title, $e->description, "/events/{$e->slug}", $e))
                ->all(),
            'total' => $query->count(),
        ];
    }

    /**
     * @param  BlogPost|Course|Event|News|null  $model  Anything with a
     *                                                  `featured_image` media collection — null for Page, which
     *                                                  has no image of its own.
     */
    private function normalize(string $type, string $title, ?string $excerpt, string $url, $model): array
    {
        $image = $model?->getFirstMedia('featured_image');

        return [
            'type' => $type,
            'title' => $title,
            'excerpt' => $excerpt ? str($excerpt)->limit(160)->value() : null,
            'url' => $url,
            'image_url' => $image?->hasGeneratedConversion('thumb') ? $image->getUrl('thumb') : $image?->getUrl(),
        ];
    }

    /** @return array<int, string> */
    private function requestedTypes(Request $request): array
    {
        $all = ['course', 'page', 'blog', 'news', 'event'];

        if (! $request->filled('type')) {
            return $all;
        }

        $requested = array_map('trim', explode(',', $request->string('type')->value()));

        return array_values(array_intersect($all, $requested)) ?: $all;
    }
}
