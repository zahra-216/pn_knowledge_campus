<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Course;
use App\Models\Inquiry;
use App\Models\PageView;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Milestone 24 (Dashboard Analytics) — one combined payload for every
 * chart the Dashboard shows, matching HomepageController's own
 * "composed payload" precedent (one request instead of five separate
 * waterfalls for a single screen). Gated by the existing `dashboard.view`
 * permission every role already has (see RoleSeeder) rather than a new
 * permission group — this endpoint's entire reason to exist is "what
 * the Dashboard screen shows", not a separate module.
 */
class AnalyticsController extends Controller
{
    /**
     * POST /api/v1/analytics/pageview — public, unauthenticated,
     * fire-and-forget. See PageView's migration docblock.
     */
    public function trackPageView(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'visitor_id' => ['required', 'string', 'max:64'],
        ]);

        PageView::create($request->only('path', 'visitor_id'));

        return ApiResponse::success(null, 201);
    }

    public function dashboard(Request $request): JsonResponse
    {
        Gate::authorize('dashboard.view');

        $days = max(1, min(90, $request->integer('days', 30)));
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        return ApiResponse::success([
            'range_days' => $days,
            'visitors' => $this->visitors($start, $days),
            'page_views' => $this->pageViews($start, $days),
            'applications' => $this->applications($start, $days),
            'inquiries' => $this->inquiries($start, $days),
            'popular_courses' => $this->popularCourses(),
        ]);
    }

    private function visitors(Carbon $start, int $days): array
    {
        $counts = PageView::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT visitor_id) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return $this->fillDateRange($counts, $days);
    }

    private function pageViews(Carbon $start, int $days): array
    {
        $counts = PageView::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $topPages = PageView::where('created_at', '>=', $start)
            ->selectRaw('path, COUNT(*) as count')
            ->groupBy('path')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['path' => $row->path, 'count' => (int) $row->count]);

        return [...$this->fillDateRange($counts, $days), 'top_pages' => $topPages];
    }

    private function applications(Carbon $start, int $days): array
    {
        $counts = Application::where('status', '!=', 'draft')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $byStatus = Application::where('status', '!=', 'draft')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [...$this->fillDateRange($counts, $days), 'by_status' => $byStatus];
    }

    private function inquiries(Carbon $start, int $days): array
    {
        $counts = Inquiry::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $byStatus = Inquiry::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');

        return [...$this->fillDateRange($counts, $days), 'by_status' => $byStatus];
    }

    /**
     * "Popular" is ranked by real conversion signals already in the
     * database (how many inquiries/applications a course generated),
     * not raw page views — a course visitors keep enquiring/applying to
     * is a stronger "popular" signal for a Dashboard than a raw view
     * count, and needs no new tracking beyond what Milestones 19/20
     * already capture.
     */
    private function popularCourses(): Collection
    {
        $inquiryCounts = Inquiry::whereNotNull('course_id')->selectRaw('course_id, COUNT(*) as count')->groupBy('course_id')->pluck('count', 'course_id');
        $applicationCounts = Application::whereNotNull('course_id')->where('status', '!=', 'draft')
            ->selectRaw('course_id, COUNT(*) as count')->groupBy('course_id')->pluck('count', 'course_id');

        $combined = [];
        foreach ($inquiryCounts as $courseId => $count) {
            $combined[$courseId] = ($combined[$courseId] ?? 0) + $count;
        }
        foreach ($applicationCounts as $courseId => $count) {
            $combined[$courseId] = ($combined[$courseId] ?? 0) + $count;
        }
        arsort($combined);
        $topIds = array_slice(array_keys($combined), 0, 5, true);

        if (empty($topIds)) {
            return collect();
        }

        $courses = Course::whereIn('id', $topIds)->get()->keyBy('id');

        return collect($topIds)
            ->filter(fn ($id) => $courses->has($id))
            ->map(fn ($id) => [
                'course_name' => $courses[$id]->course_name,
                'slug' => $courses[$id]->slug,
                'count' => $combined[$id],
            ])
            ->values();
    }

    private function fillDateRange(Collection $counts, int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $labels[] = $date;
            $data[] = (int) ($counts[$date] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
