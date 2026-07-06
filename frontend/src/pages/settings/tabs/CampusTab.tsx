import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import { BranchesPanel } from "../components/BranchesPanel";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  { key: "campus_name", label: "Campus Name" },
  { key: "campus_short_name", label: "Short Name", hint: "Used where space is limited (mobile header, favicon tooltip)." },
  { key: "campus_tagline", label: "Tagline" },
  { key: "registration_number", label: "Registration Number" },
  { key: "accreditation_number", label: "Accreditation Number" },
];

interface CampusTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function CampusTab({ values, isLoading, isSaving, onSave }: CampusTabProps) {
  return (
    <div className="flex flex-col gap-8">
      <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />
      <hr className="border-[color:var(--color-border)]" />
      <BranchesPanel />
    </div>
  );
}
