import type { SeoMeta } from "@/types/seo";

/** Matches config('page-blocks.types') on the backend. */
export type BlockType = "hero" | "text" | "rich_text" | "image" | "gallery" | "video" | "cta" | "faq" | "statistics" | "testimonials" | "partners" | "chairman_message" | "management_board";

export type PageStatus = "draft" | "published" | "scheduled" | "archived";

export interface HeroBlockData {
  heading: string;
  subheading?: string | null;
  media_id?: number | null;
  cta_label?: string | null;
  cta_url?: string | null;
  alignment?: "left" | "center" | "right";
}

export interface TextBlockData {
  body: string;
}

export interface ImageBlockData {
  media_id: number | null;
  caption?: string | null;
}

export interface GalleryBlockData {
  media_ids: number[];
}

export interface VideoBlockData {
  source: "upload" | "youtube" | "vimeo";
  media_id?: number | null;
  url?: string | null;
  caption?: string | null;
}

export interface CtaBlockData {
  heading: string;
  body?: string | null;
  button_label: string;
  button_url: string;
  style?: "primary" | "secondary";
}

export interface FaqItem {
  question: string;
  answer: string;
}

export interface StatisticItem {
  label: string;
  value: string;
  icon?: string | null;
}

export interface TestimonialItem {
  quote: string;
  name: string;
  role?: string | null;
  avatar_media_id?: number | null;
}

export interface PartnerItem {
  name: string;
  logo_media_id: number | null;
  url?: string | null;
}

export interface DirectorItem {
  name: string;
  position: string;
  photo_media_id: number | null;
}

/** Matches PageBlockResource on the backend. `data`'s real shape depends on `block_type`. */
export interface PageBlock {
  id: number;
  page_id: number;
  block_type: BlockType;
  data: Record<string, unknown>;
  order: number;
  is_active: boolean;
}

/** Matches PageResource on the backend. */
export interface Page {
  id: number;
  title: string;
  slug: string;
  template: string;
  status: PageStatus;
  published_at: string | null;
  blocks: PageBlock[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
  updated_at: string;
}

export type PagePayload = Partial<Pick<Page, "title" | "slug" | "template" | "status" | "published_at">>;

export interface PageBlockPayload {
  block_type?: BlockType;
  data?: Record<string, unknown>;
  order?: number;
  is_active?: boolean;
}

export interface ChairmanMessageBlockData {
  heading?: string | null;
  name: string;
  role?: string | null;
  message: string;
  media_id?: number | null;
}
