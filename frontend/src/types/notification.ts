/**
 * Milestone 23 (Notification System) — the in-app notification bell.
 * Only `NewApplicationNotification` writes to Laravel's 'database'
 * channel today (see the backend NotificationController's docblock),
 * so `data` always has this shape for now; widen this union once a
 * second 'database'-channel notification exists.
 */
export interface AppNotification {
  id: string;
  type: string;
  data: {
    title: string;
    message: string;
    url?: string;
  };
  read_at: string | null;
  created_at: string;
}

export interface NotificationFeed {
  items: AppNotification[];
  unread_count: number;
}
