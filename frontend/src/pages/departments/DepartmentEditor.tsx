import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, type TabItem } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { DepartmentDetailsTab } from "./components/DepartmentDetailsTab";
import { DepartmentBannerTab } from "./components/DepartmentBannerTab";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { Department, DepartmentPayload } from "@/types/department";
import type { Faculty } from "@/types/faculty";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "banner", label: "Banner" },
  { key: "seo", label: "SEO" },
];

/**
 * Department Management editor — a full page rather than a modal,
 * mirroring the Faculty editor's own reasoning (too many fields/tabs for
 * one form: Details incl. Faculty relationship, Banner, SEO).
 */
export function DepartmentEditor() {
  const { id } = useParams<{ id: string }>();
  const departmentId = Number(id);
  const { can } = usePermission();
  const canEdit = can("departments.edit");

  const [activeTab, setActiveTab] = useState("details");
  const [department, setDepartment] = useState<Department | null>(null);
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchDepartment = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<Department>>(ENDPOINTS.departments.admin(departmentId));
      setDepartment(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [departmentId]);

  useEffect(() => {
    if (!can("departments.view")) return;
    fetchDepartment();
    api.get<ApiCollection<Faculty>>(ENDPOINTS.faculties.admin(), { params: { per_page: 100 } }).then(({ data }) => setFaculties(data.data));
  }, [fetchDepartment, can]);

  async function handleSave(payload: DepartmentPayload) {
    const { data } = await api.put<ApiResponse<Department>>(ENDPOINTS.departments.admin(departmentId), payload);
    setDepartment(data.data);
  }

  if (!can("departments.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Departments", to: "/admin/departments" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Department Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !department) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Departments", to: "/admin/departments" }, { label: department.name }]} />
      <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{department.name}</h1>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && (
            <DepartmentDetailsTab department={department} faculties={faculties} canEdit={canEdit} onSave={handleSave} />
          )}
          {activeTab === "banner" && <DepartmentBannerTab department={department} onSave={handleSave} />}
          {activeTab === "seo" && <SeoFieldsPanel type="department" id={department.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
