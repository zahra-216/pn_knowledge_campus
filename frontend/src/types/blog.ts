import type { SeoMeta } from "@/types/seo";

export type BlogPostStatus = "draft" | "published" | "scheduled" | "archived";

/** Matches BlogCategoryResource on the backend. */
export interface BlogCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  posts_count?: number;
}

export type BlogCategoryPayload = Partial<Pick<BlogCategory, "name" | "slug" | "order">>;

/** Matches TagResource on the backend. */
export interface Tag {
  id: number;
  name: string;
  slug: string;
  posts_count?: number;
}

export type TagPayload = Partial<Pick<Tag, "name" | "slug">>;

export interface BlogPostGalleryItem {
  id: number;
  url: string;
  thumb_url: string;
}

export interface BlogPostSummary {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  category: { id: number; name: string } | null;
  featured_image_url: string | null;
  published_at: string | null;
}

/** Matches BlogPostResource on the backend. */
export interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string;
  category: { id: number; name: string; slug: string } | null;
  author: { id: number; name: string } | null;
  tags: Tag[];
  status: BlogPostStatus;
  published_at: string | null;
  is_featured: boolean;
  views_count: number;
  featured_image_url: string | null;
  gallery: BlogPostGalleryItem[];
  related_posts: BlogPostSummary[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type BlogPostPayload = Partial<
  Pick<BlogPost, "title" | "slug" | "excerpt" | "body" | "status" | "published_at" | "is_featured">
> & {
  category_id?: number | null;
  author_id?: number;
  tags?: string[];
  featured_image_media_id?: number | null;
  gallery_media_ids?: number[];
};
