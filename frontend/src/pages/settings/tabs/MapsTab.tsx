import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  {
    key: "google_maps_embed_url",
    label: "Google Maps Embed URL",
    type: "textarea",
    hint: "The src URL from Google Maps' \"Embed a map\" share option.",
  },
  {
    key: "google_maps_api_key",
    label: "Google Maps API Key",
    hint: "Restrict this key by HTTP referrer in Google Cloud Console.",
  },
];

interface MapsTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function MapsTab({ values, isLoading, isSaving, onSave }: MapsTabProps) {
  return <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />;
}
