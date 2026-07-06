import { useState, useRef, useEffect, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { Bell } from "lucide-react";
import { cn } from "@/utils/cn";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Spinner } from "@/components/ui";
import type { ApiResponse } from "@/types/api";
import type { NotificationFeed } from "@/types/notification";

const POLL_INTERVAL_MS = 60_000;

/**
 * UI/UX Design, Section 5.2 — a slide-down panel from the bell icon.
 * Milestone 23 (Notification System) replaces the placeholder empty
 * state with the real feed: polls the unread count so the badge stays
 * current even while the panel is closed, and fetches the full list
 * only when opened (no point paying for it on every poll tick).
 */
export function NotificationsPanel() {
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const [feed, setFeed] = useState<NotificationFeed>({ items: [], unread_count: 0 });
  const [isLoading, setIsLoading] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  const fetchFeed = useCallback(async () => {
    const { data } = await api.get<ApiResponse<NotificationFeed>>(ENDPOINTS.notifications.list);
    setFeed(data.data);
  }, []);

  useEffect(() => {
    fetchFeed();
    const timer = setInterval(fetchFeed, POLL_INTERVAL_MS);
    return () => clearInterval(timer);
  }, [fetchFeed]);

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  async function handleOpen() {
    setOpen((o) => !o);
    if (!open) {
      setIsLoading(true);
      await fetchFeed();
      setIsLoading(false);
    }
  }

  async function handleSelect(notificationId: string, url?: string) {
    setOpen(false);
    await api.patch(ENDPOINTS.notifications.markRead(notificationId));
    await fetchFeed();
    if (url) navigate(url);
  }

  async function handleMarkAllRead() {
    await api.patch(ENDPOINTS.notifications.markAllRead);
    await fetchFeed();
  }

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={handleOpen}
        aria-expanded={open}
        aria-label="Notifications"
        className="relative rounded p-2 hover:bg-black/5 dark:hover:bg-white/5"
      >
        <Bell className="h-5 w-5 text-navy dark:text-white" aria-hidden="true" />
        {feed.unread_count > 0 && (
          <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[10px] font-semibold text-white">
            {feed.unread_count > 9 ? "9+" : feed.unread_count}
          </span>
        )}
      </button>

      {open && (
        <div
          className={cn(
            "absolute right-0 top-full mt-1 w-80 rounded border border-[color:var(--color-border)]",
            "bg-[color:var(--color-surface)] shadow-2"
          )}
        >
          <div className="flex items-center justify-between border-b border-[color:var(--color-border)] px-4 py-3">
            <p className="text-body-sm font-semibold text-[color:var(--color-text)]">Notifications</p>
            {feed.unread_count > 0 && (
              <button type="button" onClick={handleMarkAllRead} className="text-caption font-medium text-navy hover:underline dark:text-gold">
                Mark all read
              </button>
            )}
          </div>

          <div className="max-h-96 overflow-y-auto">
            {isLoading ? (
              <div className="flex justify-center py-8">
                <Spinner />
              </div>
            ) : feed.items.length === 0 ? (
              <p className="px-4 py-6 text-body-sm text-neutral-500">Nothing yet — you'll see new activity here as it happens.</p>
            ) : (
              feed.items.map((n) => (
                <button
                  key={n.id}
                  type="button"
                  onClick={() => handleSelect(n.id, n.data.url)}
                  className={cn(
                    "flex w-full flex-col items-start gap-0.5 border-b border-[color:var(--color-border)] px-4 py-3 text-left last:border-b-0 hover:bg-navy/5 dark:hover:bg-white/10",
                    !n.read_at && "bg-navy/[0.03] dark:bg-white/[0.04]"
                  )}
                >
                  <span className="flex w-full items-center gap-2 text-body-sm font-semibold text-[color:var(--color-text)]">
                    {!n.read_at && <span className="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-gold" aria-hidden="true" />}
                    {n.data.title}
                  </span>
                  <span className="text-caption text-neutral-500">{n.data.message}</span>
                  <span className="text-caption text-neutral-400">{new Date(n.created_at).toLocaleString()}</span>
                </button>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}
