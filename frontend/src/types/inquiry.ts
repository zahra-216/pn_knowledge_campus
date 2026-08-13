export type InquiryStatus = "new" | "in_progress" | "resolved" | "spam";

/** Matches InquiryResource on the backend (Public Website milestone — see the `inquiries` migration's docblock for why this is a minimal capture-only slice). */
export interface InquiryConfirmation {
  id: number;
  name: string;
  status: InquiryStatus;
  created_at: string;
}

export interface InquiryPayload {
  name: string;
  email: string;
  phone?: string;
  message: string;
  source_page?: string;
  course_id?: number | null;
  international_applicant?: boolean;
}

/** Matches InquiryController::assignableStaff()'s plain {id, name} shape. */
export interface AssignableStaffMember {
  id: number;
  name: string;
}

/** Matches InquiryNoteResource — audit fix (High remediation), a staff follow-up note thread. */
export interface InquiryNote {
  id: number;
  body: string;
  author: { id: number; name: string } | null;
  created_at: string;
}

/** Matches InquiryAdminResource — the Inquiry Management admin inbox. */
export interface AdminInquiry {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  message: string;
  source_page: string | null;
  course: { id: number; name: string; slug: string } | null;
  international_applicant: boolean;
  status: InquiryStatus;
  assigned_to: { id: number; name: string } | null;
  notes: InquiryNote[];
  created_at: string;
}
