import type { SeoMeta } from "@/types/seo";

export type FacultyStatus = "draft" | "published";

export interface FacultyGalleryItem {
  id: number;
  url: string;
  thumb_url: string;
}

/** Lightweight department summary embedded in FacultyResource — see the backend's departments() relation. */
export interface FacultyDepartmentSummary {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  order: number;
  status: "draft" | "published";
}

/** Lightweight course summary embedded in FacultyResource — see the backend's courses() relation. */
export interface FacultyCourseSummary {
  id: number;
  course_name: string;
  slug: string;
  overview: string;
  status: string;
}

/** Matches FacultyResource on the backend. */
export interface Faculty {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  description: string | null;
  dean_name: string | null;
  dean_title: string | null;
  dean_message: string | null;
  order: number;
  status: FacultyStatus;
  icon_url: string | null;
  banner_url: string | null;
  dean_photo_url: string | null;
  gallery: FacultyGalleryItem[];
  departments: FacultyDepartmentSummary[];
  courses: FacultyCourseSummary[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type FacultyPayload = Partial<
  Pick<Faculty, "name" | "slug" | "short_description" | "description" | "dean_name" | "dean_title" | "dean_message" | "order" | "status">
> & {
  icon_media_id?: number | null;
  banner_media_id?: number | null;
  dean_photo_media_id?: number | null;
};
