import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, type TabItem } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { FacultyDetailsTab } from "./components/FacultyDetailsTab";
import { FacultyMediaTab } from "./components/FacultyMediaTab";
import { FacultyDeanTab } from "./components/FacultyDeanTab";
import type { ApiResponse } from "@/types/api";
import type { Faculty, FacultyPayload } from "@/types/faculty";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "media", label: "Banner, Icon & Gallery" },
  { key: "dean", label: "Dean" },
  { key: "seo", label: "SEO" },
];

/**
 * Faculty Management editor — a full page rather than a modal, since a
 * faculty has too many fields/media collections for one form (Details,
 * Banner/Icon/Gallery, Dean, SEO), the same reasoning as the Page
 * Builder's own editor.
 */
export function FacultyEditor() {
  const { id } = useParams<{ id: string }>();
  const facultyId = Number(id);
  const { can } = usePermission();
  const canEdit = can("faculties.edit");

  const [activeTab, setActiveTab] = useState("details");
  const [faculty, setFaculty] = useState<Faculty | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchFaculty = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<Faculty>>(ENDPOINTS.faculties.admin(facultyId));
      setFaculty(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [facultyId]);

  useEffect(() => {
    if (!can("faculties.view")) return;
    fetchFaculty();
  }, [fetchFaculty, can]);

  async function handleSave(payload: FacultyPayload) {
    const { data } = await api.put<ApiResponse<Faculty>>(ENDPOINTS.faculties.admin(facultyId), payload);
    setFaculty(data.data);
  }

  if (!can("faculties.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Faculties", to: "/admin/faculties" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Faculty Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !faculty) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Faculties", to: "/admin/faculties" }, { label: faculty.name }]} />
      <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{faculty.name}</h1>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && <FacultyDetailsTab faculty={faculty} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "media" && (
            <FacultyMediaTab faculty={faculty} canEdit={canEdit} onSave={handleSave} onRefresh={fetchFaculty} />
          )}
          {activeTab === "dean" && <FacultyDeanTab faculty={faculty} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "seo" && <SeoFieldsPanel type="faculty" id={faculty.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
