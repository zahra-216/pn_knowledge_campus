/**
 * Online Applications (Milestone 20). There is no visitor
 * authentication anywhere in this project — a draft/submitted
 * application is looked up and edited using `application_number` +
 * `email` as a matching pair (see the backend's Application model
 * docblock). `Application` is the shape returned to the VISITOR
 * (ApplicationPublicResource); `AdminApplication` is the fuller shape
 * returned to STAFF (ApplicationResource), which also includes internal
 * review fields the visitor never sees.
 */
export type ApplicationStatus = "draft" | "submitted" | "under_review" | "approved" | "rejected";

export interface ApplicationDocument {
  id: number;
  label: string;
  file_name: string;
  url: string;
  size: number;
}

export interface ApplicationCourseSummary {
  id: number;
  name: string;
  slug: string;
}

export interface Application {
  application_number: string;
  course: ApplicationCourseSummary | null;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  date_of_birth: string | null;
  nationality: string | null;
  address: string | null;
  international_applicant: boolean;
  status: ApplicationStatus;
  submitted_at: string | null;
  documents: ApplicationDocument[];
  created_at: string;
}

export interface AdminApplication extends Application {
  id: number;
  reviewed_by: { id: number; name: string } | null;
  reviewed_at: string | null;
  review_notes: string | null;
}

export type ApplicationCreatePayload = Partial<
  Pick<Application, "first_name" | "last_name" | "email" | "phone" | "date_of_birth" | "nationality" | "address" | "international_applicant">
> & { course_id?: number | null };

export type ApplicationUpdatePayload = Partial<
  Pick<Application, "first_name" | "last_name" | "phone" | "date_of_birth" | "nationality" | "address" | "international_applicant">
> & { email: string; new_email?: string; course_id?: number | null };
