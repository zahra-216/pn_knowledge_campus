import type { SeoMeta } from "@/types/seo";

export type CourseStatus = "draft" | "published" | "scheduled" | "archived";

/** Matches CourseLookupResource on the backend (CourseLevel/CourseMode). */
export interface CourseLookup {
  id: number;
  name: string;
  slug: string;
  order: number;
}

/**
 * Matches CourseCategoryResource on the backend. Category graduated
 * beyond the shared {name, slug, order} lookup shape: icon/image media,
 * a parent/child tree, and a courses_count rollup. `parent`/`children`/
 * `courses_count` are only present where the backend eager-loads them
 * (category list/show/reorder, and the public tree) — absent (not `[]`)
 * when nested inside another resource like Course, which doesn't load them.
 */
export interface CourseCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  parent_id: number | null;
  parent?: { id: number; name: string } | null;
  icon_url: string | null;
  image_url: string | null;
  courses_count?: number;
  children?: CourseCategory[];
  created_at: string;
}

export type CourseCategoryPayload = Partial<Pick<CourseCategory, "name" | "slug" | "order" | "parent_id">> & {
  icon_media_id?: number | null;
  image_media_id?: number | null;
};

export interface CourseCategoryReorderEntry {
  id: number;
  parent_id: number | null;
  order: number;
}

export interface CourseFacultySummary {
  id: number;
  name: string;
  slug: string;
}

export interface CourseDepartmentSummary {
  id: number;
  name: string;
  slug: string;
}

export interface CourseGalleryItem {
  id: number;
  url: string;
  thumb_url: string;
}

export interface CourseDownloadItem {
  id: number;
  name: string;
  url: string;
  size: number;
}

export interface CourseCurriculumItem {
  id: number;
  parent_id: number | null;
  title: string;
  description: string | null;
  duration: string | null;
  order: number;
  children: CourseCurriculumItem[];
}

export interface CourseFaq {
  id: number;
  question: string;
  answer: string;
  order: number;
  is_active: boolean;
}

/** Matches CourseResource on the backend. */
export interface Course {
  id: number;
  course_name: string;
  course_code: string;
  slug: string;
  faculty: CourseFacultySummary;
  department: CourseDepartmentSummary;
  level: { id: number; name: string };
  mode: { id: number; name: string };
  category: CourseCategory | null;
  duration_value: number;
  duration_unit: "day" | "week" | "month" | "year";
  duration: string;
  price: { amount: number | null; currency: string; discount_amount: number | null };
  overview: string;
  description: string;
  entry_requirements: string | null;
  learning_outcomes: string | null;
  career_opportunities: string | null;
  status: CourseStatus;
  published_at: string | null;
  is_featured: boolean;
  order: number;
  featured_image_url: string | null;
  gallery: CourseGalleryItem[];
  downloads: CourseDownloadItem[];
  curriculum: CourseCurriculumItem[];
  faqs: CourseFaq[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type CoursePayload = Partial<
  Pick<
    Course,
    | "course_name"
    | "course_code"
    | "slug"
    | "duration_value"
    | "duration_unit"
    | "overview"
    | "description"
    | "entry_requirements"
    | "learning_outcomes"
    | "career_opportunities"
    | "status"
    | "published_at"
    | "is_featured"
    | "order"
  >
> & {
  faculty_id?: number;
  department_id?: number;
  level_id?: number;
  mode_id?: number;
  category_id?: number | null;
  price?: number | null;
  discount_price?: number | null;
  price_currency?: string;
  featured_image_media_id?: number | null;
  gallery_media_ids?: number[];
  downloads_media_ids?: number[];
};

export type CourseCurriculumItemPayload = Partial<Pick<CourseCurriculumItem, "title" | "description" | "duration" | "order">> & {
  parent_id?: number | null;
};

export type CourseFaqPayload = Partial<Pick<CourseFaq, "question" | "answer" | "order" | "is_active">>;
