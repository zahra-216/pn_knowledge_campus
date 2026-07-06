import { useState } from "react";
import { Image as ImageIcon } from "lucide-react";
import { Button } from "@/components/ui";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import { SettingsFieldGroup, type SettingsFieldDef } from "../components/SettingsFieldGroup";
import type { SettingsMap } from "@/types/settings";
import type { MediaItem } from "@/types/media";

const FIELDS: SettingsFieldDef[] = [
  { key: "site_url", label: "Public Site URL", hint: "e.g. https://www.pnknowledgecampus.edu — used to build the XML sitemap and canonical URLs." },
  { key: "default_meta_title", label: "Default Meta Title" },
  { key: "default_meta_description", label: "Default Meta Description", type: "textarea" },
  { key: "default_keywords", label: "Default Keywords", hint: "Comma-separated." },
];

interface SeoDefaultsTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

/**
 * SEO Default Settings — fallback meta tags used when a page/entity has
 * no explicit SEO data of its own (SRS Section 7.4; config/settings.php's
 * `seo_defaults` group already existed from Milestone 1, but this is the
 * first admin UI for it).
 */
export function SeoDefaultsTab({ values, isLoading, isSaving, onSave }: SeoDefaultsTabProps) {
  const [isPickerOpen, setIsPickerOpen] = useState(false);
  const ogImageId = values.default_og_image_media_id;

  async function handleSelectImage(item: MediaItem) {
    await onSave({ default_og_image_media_id: String(item.id) });
    setIsPickerOpen(false);
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-6">
      <SettingsFieldGroup fields={FIELDS} values={values} isLoading={isLoading} isSaving={isSaving} onSave={onSave} />

      <hr className="border-[color:var(--color-border)]" />

      <div className="flex items-center gap-4">
        <div className="flex h-16 w-16 items-center justify-center rounded-md border border-[color:var(--color-border)] bg-[color:var(--color-surface-alt)]">
          <ImageIcon className="h-6 w-6 text-neutral-400" aria-hidden="true" />
        </div>
        <div className="flex flex-col gap-1">
          <p className="text-body-sm font-medium text-[color:var(--color-text)]">Default Social Share Image</p>
          <p className="text-caption text-neutral-500">{ogImageId ? `Media #${ogImageId}` : "Not set"}</p>
        </div>
        <Button variant="secondary" size="sm" className="ml-auto" isLoading={isSaving} onClick={() => setIsPickerOpen(true)}>
          Choose from Library
        </Button>
      </div>

      <MediaPickerModal open={isPickerOpen} onClose={() => setIsPickerOpen(false)} onSelect={handleSelectImage} type="image" />
    </div>
  );
}
