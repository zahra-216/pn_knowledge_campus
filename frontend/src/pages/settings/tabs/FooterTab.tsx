import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  { key: "footer_text", label: "Footer Text", type: "textarea" },
  { key: "footer_copyright", label: "Copyright Line" },
];

interface FooterTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function FooterTab({ values, isLoading, isSaving, onSave }: FooterTabProps) {
  return <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />;
}
