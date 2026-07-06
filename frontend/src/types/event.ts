import type { SeoMeta } from "@/types/seo";

export type EventStatus = "draft" | "published" | "scheduled" | "archived";

export interface EventGalleryItem {
  id: number;
  url: string;
  thumb_url: string;
}

/** Matches EventSpeakerResource on the backend — not in the Database Design document (client-requested addition). */
export interface EventSpeaker {
  id: number;
  name: string;
  title: string | null;
  bio: string | null;
  order: number;
  photo_url: string | null;
}

export type EventSpeakerPayload = Partial<Pick<EventSpeaker, "name" | "title" | "bio" | "order">> & {
  photo_media_id?: number | null;
};

/** Matches EventResource on the backend. No `author`/`excerpt` — not part of the Events schema (unlike News/Blog). */
export interface CampusEvent {
  id: number;
  title: string;
  slug: string;
  venue: string | null;
  is_online: boolean;
  starts_at: string;
  ends_at: string | null;
  is_upcoming: boolean;
  description: string;
  registration_url: string | null;
  status: EventStatus;
  published_at: string | null;
  featured_image_url: string | null;
  gallery: EventGalleryItem[];
  speakers: EventSpeaker[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type EventPayload = Partial<
  Pick<
    CampusEvent,
    "title" | "slug" | "venue" | "is_online" | "starts_at" | "ends_at" | "description" | "registration_url" | "status" | "published_at"
  >
> & {
  featured_image_media_id?: number | null;
  gallery_media_ids?: number[];
};
