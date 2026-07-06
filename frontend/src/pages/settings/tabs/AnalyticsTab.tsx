import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  { key: "ga_tracking_id", label: "Google Analytics Tracking ID", hint: "e.g. G-XXXXXXXXXX" },
  { key: "gtm_container_id", label: "Google Tag Manager Container ID", hint: "e.g. GTM-XXXXXXX" },
];

interface AnalyticsTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function AnalyticsTab({ values, isLoading, isSaving, onSave }: AnalyticsTabProps) {
  return <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />;
}
