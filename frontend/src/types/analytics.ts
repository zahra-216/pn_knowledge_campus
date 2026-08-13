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

/** Audit fix (High remediation) — FR-18's "published content counts", added to the Dashboard payload. */
export interface PublishedContentCounts {
  courses: number;
  news: number;
  blog_posts: number;
  events: number;
  pages: number;
}

export type ActivityType = "course" | "news" | "blog" | "event" | "page";

/** Audit fix (High remediation) — FR-18's "recent activity", added to the Dashboard payload. */
export interface RecentActivityItem {
  type: ActivityType;
  id: number;
  title: string;
  status: string;
  updated_at: string;
  updated_by: string | null;
  admin_url: string;
}

export interface DashboardAnalytics {
  range_days: number;
  visitors: SeriesData;
  page_views: SeriesData & { top_pages: TopPage[] };
  applications: SeriesData & { by_status: Record<string, number> };
  inquiries: SeriesData & { by_status: Record<string, number> };
  popular_courses: PopularCourse[];
  published_content_counts: PublishedContentCounts;
  recent_activity: RecentActivityItem[];
}
