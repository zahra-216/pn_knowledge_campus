import { ENDPOINTS } from "@/lib/endpoints";
import { CourseLookupPage } from "./CourseLookupPage";

export function CourseModes() {
  return <CourseLookupPage title="Course Modes" emptyDescription="Add delivery modes like Full-Time, Part-Time, Online." endpoint={ENDPOINTS.courseModes.admin} />;
}
