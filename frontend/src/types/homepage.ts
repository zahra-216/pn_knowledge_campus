/** Matches HomepageSection::SECTIONS on the backend. */
export type HomepageSectionKey =
  | "hero"
  | "welcome"
  | "featured_courses"
  | "faculties"
  | "why_choose_us"
  | "statistics"
  | "testimonials"
  | "partners"
  | "latest_news"
  | "upcoming_events"
  | "cta"
  | "footer_widgets";

/** Matches HomepageSectionResource on the backend. */
export interface HomepageSection {
  id: number;
  section_key: HomepageSectionKey;
  order: number;
  is_enabled: boolean;
}

/** Matches HeroSlideResource on the backend. */
export interface HeroSlide {
  id: number;
  title: string;
  subtitle: string | null;
  cta_text: string | null;
  cta_url: string | null;
  order: number;
  starts_at: string | null;
  ends_at: string | null;
  is_active: boolean;
  image_url: string | null;
  thumb_url: string | null;
}

export type HeroSlidePayload = Partial<
  Pick<HeroSlide, "title" | "subtitle" | "cta_text" | "cta_url" | "starts_at" | "ends_at" | "is_active" | "order">
> & { media_id?: number | null };

/** Matches TestimonialResource on the backend. */
export interface Testimonial {
  id: number;
  name: string;
  role_title: string | null;
  course_id: number | null;
  content: string;
  rating: number | null;
  is_featured: boolean;
  order: number;
  is_active: boolean;
  photo_url: string | null;
}

export type TestimonialPayload = Partial<
  Pick<Testimonial, "name" | "role_title" | "content" | "rating" | "is_featured" | "is_active">
> & { media_id?: number | null };

/** Matches PartnerCategoryResource on the backend (Milestone 16). Flat taxonomy — no hierarchy/media/SEO, same shape as BlogCategory/NewsCategory. */
export interface PartnerCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  partners_count?: number;
}

export type PartnerCategoryPayload = Partial<Pick<PartnerCategory, "name" | "order">> & { slug?: string };

/** Matches PartnerResource on the backend. */
export interface Partner {
  id: number;
  name: string;
  url: string | null;
  category: { id: number; name: string; slug: string } | null;
  order: number;
  is_active: boolean;
  logo_url: string | null;
}

export type PartnerPayload = Partial<Pick<Partner, "name" | "url" | "order" | "is_active">> & {
  category_id?: number | null;
  media_id?: number | null;
};

/**
 * Matches the per-section shape returned by GET /homepage (see
 * HomepageController::contentFor on the backend) — every section is
 * either an `items` list (hero/testimonials/partners/faculties/
 * featured_courses/latest_news/upcoming_events) or a flat `content`
 * object (welcome/cta), with why_choose_us/statistics/footer_widgets
 * being `items` of loosely-typed settings-sourced records.
 */
export interface HomepageContentSection {
  section_key: HomepageSectionKey;
  items?: unknown[];
  content?: Record<string, unknown>;
}
