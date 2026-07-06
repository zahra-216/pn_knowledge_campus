import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import { SocialLinksPanel } from "../components/SocialLinksPanel";
import { OfficeHoursPanel } from "../components/OfficeHoursPanel";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  { key: "contact_email", label: "General Contact Email", type: "email" },
  { key: "contact_phone", label: "General Contact Phone" },
  { key: "contact_address", label: "Address", type: "textarea" },
  { key: "admissions_email", label: "Admissions Email", type: "email" },
  { key: "admissions_phone", label: "Admissions Phone" },
];

interface ContactTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function ContactTab({ values, isLoading, isSaving, onSave }: ContactTabProps) {
  return (
    <div className="flex flex-col gap-8">
      <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />
      <hr className="border-[color:var(--color-border)]" />
      <SocialLinksPanel />
      <hr className="border-[color:var(--color-border)]" />
      <OfficeHoursPanel />
    </div>
  );
}
