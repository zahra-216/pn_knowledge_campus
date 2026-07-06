import { useState } from "react";
import { Lock } from "lucide-react";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, useToast, type TabItem } from "@/components/ui";
import { useSettings } from "@/hooks/useSettings";
import { usePermission } from "@/hooks/usePermission";
import { CampusTab } from "./tabs/CampusTab";
import { ContactTab } from "./tabs/ContactTab";
import { SmtpTab } from "./tabs/SmtpTab";
import { BrandingTab } from "./tabs/BrandingTab";
import { FooterTab } from "./tabs/FooterTab";
import { AnalyticsTab } from "./tabs/AnalyticsTab";
import { MapsTab } from "./tabs/MapsTab";
import { SeoDefaultsTab } from "./tabs/SeoDefaultsTab";

const TABS: TabItem[] = [
  { key: "campus", label: "Campus & Branches" },
  { key: "contact", label: "Contact & Social" },
  { key: "smtp", label: "SMTP" },
  { key: "branding", label: "Logo & Favicon" },
  { key: "footer", label: "Footer" },
  { key: "analytics", label: "Analytics" },
  { key: "maps", label: "Maps" },
  { key: "seo", label: "SEO Defaults" },
];

/**
 * SRS Permission Matrix (Section 10) — Settings is Super Admin only.
 * Development Roadmap, Milestone 1 — "Settings screen live and editable
 * by Super Admin/Administrator" (the Roadmap's own summary text repeats
 * the API Design's SA/Admin phrasing; this implementation follows the
 * stricter SRS matrix instead — see the Milestone 1 plan notes).
 *
 * Maps and SEO Defaults tabs are a Settings-module extension beyond the
 * UI/UX Design document's six-tab sitemap (Section 2.2) — Office Hours
 * lives inside "Contact & Social" instead of getting a seventh/eighth
 * tab of its own, keeping tab growth to the two areas that didn't
 * naturally fit an existing tab.
 */
export function Settings() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const [activeTab, setActiveTab] = useState("campus");
  const { settings, isLoading, isSaving, save } = useSettings();

  if (!can("settings.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Settings" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            Only Super Admins can access Settings.
          </div>
        </Card>
      </div>
    );
  }

  async function handleSave(changed: Record<string, string>) {
    try {
      await save(changed);
      showToast("Settings saved.", "success");
    } catch {
      showToast("Could not save settings.", "error");
    }
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Settings" }]} />
      <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Settings</h1>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "campus" && <CampusTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "contact" && <ContactTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "smtp" && <SmtpTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "branding" && <BrandingTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "footer" && <FooterTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "analytics" && <AnalyticsTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "maps" && <MapsTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
          {activeTab === "seo" && <SeoDefaultsTab values={settings} isLoading={isLoading} isSaving={isSaving} onSave={handleSave} />}
        </Tabs>
      </Card>
    </div>
  );
}
