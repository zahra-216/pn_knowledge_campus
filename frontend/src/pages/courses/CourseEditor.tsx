import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock, UploadCloud } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, Button, Badge, type TabItem } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { CourseDetailsTab } from "./components/CourseDetailsTab";
import { CourseContentTab } from "./components/CourseContentTab";
import { CourseCurriculumTab } from "./components/CourseCurriculumTab";
import { CourseMediaTab } from "./components/CourseMediaTab";
import { CourseFaqsTab } from "./components/CourseFaqsTab";
import type { ApiResponse } from "@/types/api";
import type { Course, CoursePayload, CourseStatus } from "@/types/course";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "content", label: "Content" },
  { key: "curriculum", label: "Curriculum" },
  { key: "media", label: "Media" },
  { key: "faqs", label: "FAQs" },
  { key: "seo", label: "SEO" },
];

const STATUS_TONE: Record<CourseStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * Course Management editor — a full page with six tabs, since Course
 * has the largest field set of any module in this project (SRS Section
 * 4.3): Details (incl. Faculty/Department/Level/Mode/Category and
 * pricing), Content (overview/description/entry requirements/learning
 * outcomes/career opportunities), Curriculum, Media (featured
 * image/gallery/downloads), FAQs, and SEO.
 */
export function CourseEditor() {
  const { id } = useParams<{ id: string }>();
  const courseId = Number(id);
  const { can } = usePermission();
  const canEdit = can("courses.edit");
  const canPublish = can("courses.publish");

  const [activeTab, setActiveTab] = useState("details");
  const [course, setCourse] = useState<Course | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchCourse = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<Course>>(ENDPOINTS.courses.admin(courseId));
      setCourse(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [courseId]);

  useEffect(() => {
    if (!can("courses.view")) return;
    fetchCourse();
  }, [fetchCourse, can]);

  async function handleSave(payload: CoursePayload) {
    const { data } = await api.put<ApiResponse<Course>>(ENDPOINTS.courses.admin(courseId), payload);
    setCourse(data.data);
  }

  async function handlePublish() {
    await api.patch(ENDPOINTS.courses.publish(courseId));
    await fetchCourse();
  }

  if (!can("courses.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Courses", to: "/admin/courses" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Course Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !course) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Courses", to: "/admin/courses" }, { label: course.course_name }]} />

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{course.course_name}</h1>
          <Badge tone={STATUS_TONE[course.status]}>{course.status}</Badge>
        </div>
        {canPublish && course.status !== "published" && (
          <Button variant="secondary" onClick={handlePublish}>
            <UploadCloud className="h-4 w-4" aria-hidden="true" />
            Publish
          </Button>
        )}
      </div>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && <CourseDetailsTab course={course} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "content" && <CourseContentTab course={course} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "curriculum" && <CourseCurriculumTab courseId={course.id} canEdit={canEdit} />}
          {activeTab === "media" && <CourseMediaTab course={course} canEdit={canEdit} onSave={handleSave} onRefresh={fetchCourse} />}
          {activeTab === "faqs" && <CourseFaqsTab courseId={course.id} canEdit={canEdit} />}
          {activeTab === "seo" && <SeoFieldsPanel type="course" id={course.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
