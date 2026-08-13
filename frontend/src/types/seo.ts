/** Matches SeoMetaResource on the backend (API Design, Section 8.8). */
export interface SeoMeta {
  id: number;
  seoable_type: string;
  seoable_id: number;
  seo_title: string | null;
  meta_description: string | null;
  keywords: string | null;
  canonical_url: string | null;
  schema_type: string | null;
  og_title: string | null;
  og_description: string | null;
  og_image_media_id: number | null;
  twitter_title: string | null;
  twitter_description: string | null;
  twitter_image_media_id: number | null;
  robots_index: boolean;
  robots_follow: boolean;
}

export type SeoMetaPayload = Partial<
  Pick<
    SeoMeta,
    | "seo_title"
    | "meta_description"
    | "keywords"
    | "canonical_url"
    | "schema_type"
    | "og_title"
    | "og_description"
    | "og_image_media_id"
    | "twitter_title"
    | "twitter_description"
    | "twitter_image_media_id"
    | "robots_index"
    | "robots_follow"
  >
>;

/** Matches config('seo.seoable_types') keys on the backend. */
export type SeoableType = "faculty" | "department" | "course" | "course-category" | "blog" | "news" | "event" | "page";

/** Matches SeoMetaController::index()'s per-type summary row (SEO Manager overview). */
export interface SeoTypeSummary {
  type: SeoableType;
  label: string;
  total: number;
  with_seo: number;
  missing: number;
}

/** Matches SeoEntityResource — one row in a type's drill-down list. */
export interface SeoEntitySummary {
  id: number;
  label: string;
  slug: string | null;
  has_seo: boolean;
  seo_title: string | null;
  robots_index: boolean;
}
