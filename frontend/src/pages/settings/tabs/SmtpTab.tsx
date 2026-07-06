import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import type { SettingsMap } from "@/types/settings";

const FIELDS: SettingsFieldDef[] = [
  { key: "smtp_host", label: "SMTP Host" },
  { key: "smtp_port", label: "SMTP Port" },
  { key: "smtp_username", label: "SMTP Username" },
  { key: "smtp_password", label: "SMTP Password", type: "password" },
  { key: "smtp_encryption", label: "Encryption", hint: "tls, ssl, or leave blank for none" },
  { key: "mail_from_address", label: "\"From\" Address", type: "email" },
  { key: "mail_from_name", label: "\"From\" Name" },
];

interface SmtpTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

export function SmtpTab({ values, isLoading, isSaving, onSave }: SmtpTabProps) {
  return <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />;
}
