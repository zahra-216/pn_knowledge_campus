import type { SeoMeta } from "@/types/seo";

export type DepartmentStatus = "draft" | "published";

export interface DepartmentFacultySummary {
  id: number;
  name: string;
  slug: string;
}

/** Lightweight course summary embedded in DepartmentResource — see the backend's courses() relation. */
export interface DepartmentCourseSummary {
  id: number;
  course_name: string;
  slug: string;
  overview: string;
  status: string;
}

/** Matches DepartmentResource on the backend. */
export interface Department {
  id: number;
  faculty_id: number;
  faculty: DepartmentFacultySummary;
  name: string;
  slug: string;
  short_description: string | null;
  description: string | null;
  order: number;
  status: DepartmentStatus;
  banner_url: string | null;
  courses: DepartmentCourseSummary[];
  /** Only present on the public detail (publicShow) response. */
  seo?: SeoMeta | null;
  created_at: string;
}

export type DepartmentPayload = Partial<
  Pick<Department, "faculty_id" | "name" | "slug" | "short_description" | "description" | "order" | "status">
> & {
  banner_media_id?: number | null;
};
