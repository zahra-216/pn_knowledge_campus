import type { SeoMeta } from "@/types/seo";

export type NewsStatus = "draft" | "published" | "scheduled" | "archived";

/** Matches NewsCategoryResource on the backend. */
export interface NewsCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  news_count?: number;
}

export type NewsCategoryPayload = Partial<Pick<NewsCategory, "name" | "slug" | "order">>;

export interface NewsGalleryItem {
  id: number;
  url: string;
  thumb_url: string;
}

/** Matches NewsResource on the backend. No tags/related_posts — not requested features for News (unlike Blog). */
export interface NewsArticle {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string;
  category: { id: number; name: string; slug: string } | null;
  author: { id: number; name: string } | null;
  status: NewsStatus;
  published_at: string | null;
  is_featured: boolean;
  views_count: number;
  featured_image_url: string | null;
  gallery: NewsGalleryItem[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type NewsArticlePayload = Partial<
  Pick<NewsArticle, "title" | "slug" | "excerpt" | "body" | "status" | "published_at" | "is_featured">
> & {
  category_id?: number | null;
  author_id?: number;
  featured_image_media_id?: number | null;
  gallery_media_ids?: number[];
};
