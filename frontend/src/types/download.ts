/**
 * The standalone Downloads catalog (Milestone 18) — Prospectus,
 * Application Forms, Brochures, and other public documents. Distinct
 * from `CourseDownloadItem` in types/course.ts, which is a course's own
 * per-course brochure collection (unrelated table, unrelated endpoints).
 */

/** Matches DownloadCategoryResource on the backend. Flat taxonomy — same shape as BlogCategory/PartnerCategory/FaqCategory. */
export interface DownloadCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  downloads_count?: number;
}

export type DownloadCategoryPayload = Partial<Pick<DownloadCategory, "name" | "order">> & { slug?: string };

/** Matches DownloadResource on the backend. */
export interface Download {
  id: number;
  title: string;
  description: string | null;
  category: { id: number; name: string; slug: string } | null;
  file_url: string | null;
  file_name: string | null;
  file_size: number | null;
  file_type: string | null;
  order: number;
  is_active: boolean;
}

export type DownloadPayload = Partial<Pick<Download, "title" | "description" | "order" | "is_active">> & {
  category_id?: number | null;
  media_id?: number | null;
};
