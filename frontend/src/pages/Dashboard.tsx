import { useEffect, useState, type ReactNode } from "react";
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { Link } from "react-router-dom";
import { Users, Eye, ClipboardList, Mail, TrendingUp, GraduationCap, Newspaper, FileText, CalendarDays, Files, History } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Spinner, Badge } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import type { ApiResponse } from "@/types/api";
import type { ActivityType, DashboardAnalytics } from "@/types/analytics";

const RANGE_OPTIONS = [7, 30, 90] as const;

const CHART_COLORS = ["#1B2A4A", "#A6812C", "#2A5DAB", "#1B6B2E", "#8A6D00", "#B3261E"];

const STATUS_LABEL: Record<string, string> = {
  new: "New",
  in_progress: "In Progress",
  resolved: "Resolved",
  spam: "Spam",
  submitted: "Submitted",
  under_review: "Under Review",
  approved: "Approved",
  rejected: "Rejected",
};

const CONTENT_STATUS_TONE: Record<string, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

const ACTIVITY_TYPE_LABEL: Record<ActivityType, string> = {
  course: "Course",
  news: "News",
  blog: "Blog",
  event: "Event",
  page: "Page",
};

function timeAgo(dateString: string): string {
  const seconds = Math.floor((Date.now() - new Date(dateString).getTime()) / 1000);
  if (seconds < 60) return "just now";
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  return `${days}d ago`;
}

function formatDateLabel(date: string): string {
  return new Date(date).toLocaleDateString(undefined, { month: "short", day: "numeric" });
}

function sum(values: number[]): number {
  return values.reduce((a, b) => a + b, 0);
}

/**
 * Milestone 24 (Dashboard Analytics) — replaces the foundation-build
 * placeholder with the five real charts: Visitors, Page Views,
 * Applications, Popular Courses, Inquiry Statistics. All backed by one
 * combined GET /admin/analytics/dashboard request (see
 * AnalyticsController's docblock for why this isn't five separate
 * calls).
 */
export function Dashboard() {
  const { user } = useAuth();
  const [days, setDays] = useState<number>(30);
  const [analytics, setAnalytics] = useState<DashboardAnalytics | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setIsLoading(true);
    api
      .get<ApiResponse<DashboardAnalytics>>(ENDPOINTS.analytics.dashboard(days))
      .then(({ data }) => setAnalytics(data.data))
      .finally(() => setIsLoading(false));
  }, [days]);

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Dashboard" }]} />

      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">
          Welcome, {user?.name.split(" ")[0]}
        </h1>

        <div className="flex gap-1 rounded border border-[color:var(--color-border)] p-1">
          {RANGE_OPTIONS.map((option) => (
            <button
              key={option}
              type="button"
              onClick={() => setDays(option)}
              className={`rounded px-3 py-1 text-body-sm font-medium transition-colors ${
                days === option ? "bg-navy text-white" : "text-neutral-500 hover:bg-navy/5"
              }`}
            >
              {option}d
            </button>
          ))}
        </div>
      </div>

      {isLoading || !analytics ? (
        <div className="flex justify-center py-24">
          <Spinner className="h-8 w-8" />
        </div>
      ) : (
        <>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard icon={Users} label="Unique Visitors" value={sum(analytics.visitors.data)} />
            <StatCard icon={Eye} label="Page Views" value={sum(analytics.page_views.data)} />
            <StatCard icon={ClipboardList} label="Applications" value={sum(analytics.applications.data)} />
            <StatCard icon={Mail} label="Inquiries" value={sum(analytics.inquiries.data)} />
          </div>

          {/* Audit fix (High remediation) — FR-18's "published content counts", the other half of the Dashboard's brief this screen never covered before. */}
          <div className="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <StatCard icon={GraduationCap} label="Published Courses" value={analytics.published_content_counts.courses} />
            <StatCard icon={Newspaper} label="Published News" value={analytics.published_content_counts.news} />
            <StatCard icon={FileText} label="Published Blog Posts" value={analytics.published_content_counts.blog_posts} />
            <StatCard icon={CalendarDays} label="Published Events" value={analytics.published_content_counts.events} />
            <StatCard icon={Files} label="Published Pages" value={analytics.published_content_counts.pages} />
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            <ChartCard title="Visitors">
              <ResponsiveContainer width="100%" height={240}>
                <AreaChart data={toChartData(analytics.visitors)}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--color-border)" />
                  <XAxis dataKey="label" tick={{ fontSize: 12 }} />
                  <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Area type="monotone" dataKey="value" stroke="#1B2A4A" fill="#1B2A4A" fillOpacity={0.15} name="Visitors" />
                </AreaChart>
              </ResponsiveContainer>
            </ChartCard>

            <ChartCard title="Page Views">
              <ResponsiveContainer width="100%" height={240}>
                <LineChart data={toChartData(analytics.page_views)}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--color-border)" />
                  <XAxis dataKey="label" tick={{ fontSize: 12 }} />
                  <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Line type="monotone" dataKey="value" stroke="#A6812C" strokeWidth={2} dot={false} name="Page Views" />
                </LineChart>
              </ResponsiveContainer>
              {analytics.page_views.top_pages.length > 0 && (
                <div className="mt-4 flex flex-col gap-1.5 border-t border-[color:var(--color-border)] pt-4">
                  <p className="text-caption font-semibold uppercase tracking-wide text-neutral-500">Top Pages</p>
                  {analytics.page_views.top_pages.slice(0, 5).map((page) => (
                    <div key={page.path} className="flex items-center justify-between text-body-sm">
                      <span className="truncate text-[color:var(--color-text)]">{page.path}</span>
                      <span className="text-neutral-500">{page.count}</span>
                    </div>
                  ))}
                </div>
              )}
            </ChartCard>

            <ChartCard title="Applications">
              <ResponsiveContainer width="100%" height={200}>
                <BarChart data={toChartData(analytics.applications)}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--color-border)" />
                  <XAxis dataKey="label" tick={{ fontSize: 12 }} />
                  <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Bar dataKey="value" fill="#2A5DAB" name="Applications" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
              <StatusBreakdown byStatus={analytics.applications.by_status} />
            </ChartCard>

            <ChartCard title="Inquiry Statistics">
              <ResponsiveContainer width="100%" height={200}>
                <BarChart data={toChartData(analytics.inquiries)}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--color-border)" />
                  <XAxis dataKey="label" tick={{ fontSize: 12 }} />
                  <YAxis allowDecimals={false} tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Bar dataKey="value" fill="#1B6B2E" name="Inquiries" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
              <StatusBreakdown byStatus={analytics.inquiries.by_status} />
            </ChartCard>
          </div>

          <ChartCard title="Popular Courses" icon={TrendingUp}>
            {analytics.popular_courses.length === 0 ? (
              <p className="py-8 text-center text-body-sm text-neutral-500">
                No inquiries or applications yet — popularity is ranked by how many of each a course has received.
              </p>
            ) : (
              <ResponsiveContainer width="100%" height={Math.max(160, analytics.popular_courses.length * 48)}>
                <BarChart data={analytics.popular_courses} layout="vertical" margin={{ left: 24 }}>
                  <CartesianGrid strokeDasharray="3 3" stroke="var(--color-border)" />
                  <XAxis type="number" allowDecimals={false} tick={{ fontSize: 12 }} />
                  <YAxis type="category" dataKey="course_name" width={200} tick={{ fontSize: 12 }} />
                  <Tooltip />
                  <Bar dataKey="count" fill="#A6812C" name="Interest" radius={[0, 4, 4, 0]} />
                </BarChart>
              </ResponsiveContainer>
            )}
          </ChartCard>

          {/* Audit fix (High remediation) — FR-18's "recent activity", the other half of the Dashboard's brief this screen never covered before. */}
          <ChartCard title="Recent Activity" icon={History}>
            {analytics.recent_activity.length === 0 ? (
              <p className="py-8 text-center text-body-sm text-neutral-500">No content has been created or edited yet.</p>
            ) : (
              <ul className="flex flex-col divide-y divide-[color:var(--color-border)]">
                {analytics.recent_activity.map((item) => (
                  <li key={`${item.type}-${item.id}`} className="flex flex-wrap items-center justify-between gap-2 py-2.5">
                    <div className="flex items-center gap-3">
                      <span className="rounded bg-navy/10 px-2 py-0.5 text-caption font-medium text-navy dark:bg-white/10 dark:text-white">
                        {ACTIVITY_TYPE_LABEL[item.type]}
                      </span>
                      <Link to={item.admin_url} className="text-body-sm font-medium text-[color:var(--color-text)] hover:underline">
                        {item.title}
                      </Link>
                      <Badge tone={CONTENT_STATUS_TONE[item.status] ?? "neutral"}>{item.status}</Badge>
                    </div>
                    <div className="flex items-center gap-2 text-caption text-neutral-500">
                      {item.updated_by && <span>{item.updated_by}</span>}
                      <span>{timeAgo(item.updated_at)}</span>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </ChartCard>
        </>
      )}
    </div>
  );
}

function toChartData(series: { labels: string[]; data: number[] }) {
  return series.labels.map((label, i) => ({ label: formatDateLabel(label), value: series.data[i] }));
}

function StatCard({ icon: Icon, label, value }: { icon: typeof Users; label: string; value: number }) {
  return (
    <Card className="flex items-center gap-4">
      <div className="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-navy/10 text-navy dark:bg-white/10 dark:text-white">
        <Icon className="h-5 w-5" />
      </div>
      <div>
        <p className="font-display text-h3 font-semibold text-[color:var(--color-text)]">{value.toLocaleString()}</p>
        <p className="text-body-sm text-neutral-500">{label}</p>
      </div>
    </Card>
  );
}

function ChartCard({ title, icon: Icon, children }: { title: string; icon?: typeof Users; children: ReactNode }) {
  return (
    <Card>
      <div className="mb-3 flex items-center gap-2">
        {Icon && <Icon className="h-4 w-4 text-neutral-500" />}
        <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{title}</h2>
      </div>
      {children}
    </Card>
  );
}

function StatusBreakdown({ byStatus }: { byStatus: Record<string, number> }) {
  const entries = Object.entries(byStatus).filter(([, count]) => count > 0);
  if (entries.length === 0) return null;

  const data = entries.map(([status, count]) => ({ name: STATUS_LABEL[status] ?? status, value: count }));

  return (
    <div className="mt-4 flex items-center gap-4 border-t border-[color:var(--color-border)] pt-4">
      <ResponsiveContainer width={100} height={100}>
        <PieChart>
          <Pie data={data} dataKey="value" nameKey="name" innerRadius={25} outerRadius={45}>
            {data.map((_, i) => (
              <Cell key={i} fill={CHART_COLORS[i % CHART_COLORS.length]} />
            ))}
          </Pie>
        </PieChart>
      </ResponsiveContainer>
      <div className="flex flex-col gap-1">
        {data.map((entry, i) => (
          <div key={entry.name} className="flex items-center gap-2 text-body-sm">
            <span className="h-2.5 w-2.5 flex-shrink-0 rounded-full" style={{ backgroundColor: CHART_COLORS[i % CHART_COLORS.length] }} />
            <span className="text-[color:var(--color-text)]">{entry.name}</span>
            <span className="text-neutral-500">({entry.value})</span>
          </div>
        ))}
      </div>
    </div>
  );
}
