import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, type TabItem } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { CourseCategoryDetailsTab } from "./components/CourseCategoryDetailsTab";
import { CourseCategoryMediaTab } from "./components/CourseCategoryMediaTab";
import type { ApiResponse } from "@/types/api";
import type { CourseCategory, CourseCategoryPayload } from "@/types/course";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "media", label: "Icon & Image" },
  { key: "seo", label: "SEO" },
];

/**
 * Course Category editor — a full page (Details/Media/SEO), the same
 * reasoning as Faculty/Department's own editors: too many fields/media
 * collections for one modal now that Category carries icon/image/SEO
 * and a parent/child tree.
 */
export function CourseCategoryEditor() {
  const { id } = useParams<{ id: string }>();
  const categoryId = Number(id);
  const { can } = usePermission();
  const canEdit = can("courses.edit");

  const [activeTab, setActiveTab] = useState("details");
  const [category, setCategory] = useState<CourseCategory | null>(null);
  const [allCategories, setAllCategories] = useState<CourseCategory[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchCategory = useCallback(async () => {
    setIsLoading(true);
    try {
      const [{ data: categoryData }, { data: listData }] = await Promise.all([
        api.get<ApiResponse<CourseCategory>>(ENDPOINTS.courseCategories.admin(categoryId)),
        api.get<ApiResponse<CourseCategory[]>>(ENDPOINTS.courseCategories.admin()),
      ]);
      setCategory(categoryData.data);
      setAllCategories(listData.data);
    } finally {
      setIsLoading(false);
    }
  }, [categoryId]);

  useEffect(() => {
    if (!can("courses.view")) return;
    fetchCategory();
  }, [fetchCategory, can]);

  async function handleSave(payload: CourseCategoryPayload) {
    const { data } = await api.put<ApiResponse<CourseCategory>>(ENDPOINTS.courseCategories.admin(categoryId), payload);
    setCategory(data.data);
  }

  if (!can("courses.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Course Categories", to: "/admin/course-categories" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Course Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !category) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Course Categories", to: "/admin/course-categories" }, { label: category.name }]} />
      <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{category.name}</h1>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && (
            <CourseCategoryDetailsTab category={category} allCategories={allCategories} canEdit={canEdit} onSave={handleSave} />
          )}
          {activeTab === "media" && <CourseCategoryMediaTab category={category} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "seo" && <SeoFieldsPanel type="course-category" id={category.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
