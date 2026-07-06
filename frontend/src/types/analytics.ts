/** Milestone 24 (Dashboard Analytics) — matches AnalyticsController::dashboard()'s combined payload. */
export interface SeriesData {
  labels: string[];
  data: number[];
}

export interface TopPage {
  path: string;
  count: number;
}

export interface PopularCourse {
  course_name: string;
  slug: string;
  count: number;
}

export interface DashboardAnalytics {
  range_days: number;
  visitors: SeriesData;
  page_views: SeriesData & { top_pages: TopPage[] };
  applications: SeriesData & { by_status: Record<string, number> };
  inquiries: SeriesData & { by_status: Record<string, number> };
  popular_courses: PopularCourse[];
}
