import { useEffect, useState } from "react";
import { Button, Input, Textarea } from "@/components/ui";
import type { SettingsMap } from "@/types/settings";

export interface SettingsFieldDef {
  key: string;
  label: string;
  type?: "text" | "email" | "textarea" | "password";
  hint?: string;
}

interface SettingsFieldGroupProps {
  fields: SettingsFieldDef[];
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

/**
 * Shared rendering for a Settings tab's plain key/value fields (Campus,
 * Contact, SMTP, Footer, Analytics all follow this identical shape —
 * only the field list differs per tab). Only the fields the user
 * actually edited are sent to the save handler, matching the bulk-update
 * endpoint's partial-update contract.
 */
export function SettingsFieldGroup({ fields, values, isLoading, isSaving, onSave }: SettingsFieldGroupProps) {
  const [draft, setDraft] = useState<Record<string, string>>({});

  useEffect(() => {
    const next: Record<string, string> = {};
    for (const field of fields) {
      const value = values[field.key];
      next[field.key] = value === null || value === undefined ? "" : String(value);
    }
    setDraft(next);
  }, [fields, values]);

  async function handleSave() {
    await onSave(draft);
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {fields.map((field) => {
        const Component = field.type === "textarea" ? Textarea : Input;
        return (
          <Component
            key={field.key}
            label={field.label}
            hint={field.hint}
            type={field.type === "textarea" ? undefined : field.type ?? "text"}
            value={draft[field.key] ?? ""}
            onChange={(e) => setDraft({ ...draft, [field.key]: e.target.value })}
          />
        );
      })}
      <Button onClick={handleSave} isLoading={isSaving} className="self-start">
        Save Changes
      </Button>
    </div>
  );
}
