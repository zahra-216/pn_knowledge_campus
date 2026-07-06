import { useCallback, useEffect, useState } from "react";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, useToast, type TabItem } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { HomepageSectionList } from "./components/HomepageSectionList";
import { HomepageContentTab } from "./components/HomepageContentTab";
import type { ApiResponse } from "@/types/api";
import type { HomepageSection } from "@/types/homepage";

const TABS: TabItem[] = [
  { key: "sections", label: "Sections" },
  { key: "content", label: "Content" },
];

/**
 * Homepage Builder (SRS FR-21; Database Design's homepage_sections
 * table) — "Sections" toggles/reorders the 12 requested sections;
 * "Content" edits the flat copy (Welcome/Why Choose Us/Statistics/CTA/
 * Footer Widgets) that has no dedicated content table. Hero Slider,
 * Testimonials, and Partners are managed on their own screens (each has
 * its own SRS Permission Matrix row and is a real CRUD module, not just
 * homepage composition).
 */
export function Homepage() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canEdit = can("homepage.edit");
  const [activeTab, setActiveTab] = useState("sections");

  const [sections, setSections] = useState<HomepageSection[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchSections = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<HomepageSection[]>>(ENDPOINTS.homepage.sections);
      setSections(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("homepage.view")) return;
    fetchSections();
  }, [fetchSections, can]);

  async function persist(next: HomepageSection[]) {
    setSections(next);
    try {
      await api.patch(ENDPOINTS.homepage.reorderSections, {
        sections: next.map((s, index) => ({ section_key: s.section_key, order: index, is_enabled: s.is_enabled })),
      });
    } catch {
      showToast("Could not save section changes.", "error");
      fetchSections();
    }
  }

  if (!can("homepage.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Homepage" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to the Homepage Builder.
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Homepage" }]} />
      <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Homepage</h1>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "sections" &&
            (isLoading ? (
              <p className="text-body-sm text-neutral-500">Loading...</p>
            ) : (
              <HomepageSectionList sections={sections} canEdit={canEdit} onChange={persist} />
            ))}
          {activeTab === "content" && <HomepageContentTab canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
