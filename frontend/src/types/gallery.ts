export type GalleryItemType = "image" | "video";

/** Matches GalleryMediaItemResource on the backend. Caption lives in Spatie's per-file custom_properties, not a database column. */
export interface GalleryItem {
  id: number;
  type: GalleryItemType;
  mime_type: string | null;
  url: string;
  thumb_url: string | null;
  caption: string | null;
  order: number;
}

/** Matches GalleryAlbumResource on the backend. No `seo` — Gallery Albums is deliberately excluded from SEO (unlike every other Engagement Content module). */
export interface GalleryAlbum {
  id: number;
  title: string;
  slug: string;
  description: string | null;
  order: number;
  is_active: boolean;
  cover_url: string | null;
  items_count?: number;
  items: GalleryItem[];
  created_at: string;
}

export type GalleryAlbumPayload = Partial<Pick<GalleryAlbum, "title" | "slug" | "description" | "order" | "is_active">>;

export interface GalleryAlbumMediaItemInput {
  media_id: number;
  caption?: string | null;
}
