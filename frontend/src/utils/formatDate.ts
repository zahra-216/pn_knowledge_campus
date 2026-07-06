/**
 * Every date from the API is ISO 8601 UTC (API Design, Section 4.4).
 * This is the one place that gets converted to the visitor/staff
 * member's local time for display — components should never call
 * `new Date()` formatting logic themselves.
 */
export function formatDate(iso: string | null | undefined, opts: Intl.DateTimeFormatOptions = {}): string {
  if (!iso) return "—";

  return new Intl.DateTimeFormat("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
    ...opts,
  }).format(new Date(iso));
}

export function formatDateTime(iso: string | null | undefined): string {
  return formatDate(iso, { hour: "2-digit", minute: "2-digit" });
}

export function formatRelative(iso: string | null | undefined): string {
  if (!iso) return "—";

  const diffMs = new Date(iso).getTime() - Date.now();
  const diffMinutes = Math.round(diffMs / 60000);
  const formatter = new Intl.RelativeTimeFormat("en-GB", { numeric: "auto" });

  if (Math.abs(diffMinutes) < 60) return formatter.format(diffMinutes, "minute");
  const diffHours = Math.round(diffMinutes / 60);
  if (Math.abs(diffHours) < 24) return formatter.format(diffHours, "hour");
  const diffDays = Math.round(diffHours / 24);
  return formatter.format(diffDays, "day");
}
