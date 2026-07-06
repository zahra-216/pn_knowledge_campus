import { ENDPOINTS } from "@/lib/endpoints";
import { CourseLookupPage } from "./CourseLookupPage";

export function CourseLevels() {
  return <CourseLookupPage title="Course Levels" emptyDescription="Add levels like Certificate, Diploma, Undergraduate." endpoint={ENDPOINTS.courseLevels.admin} />;
}
