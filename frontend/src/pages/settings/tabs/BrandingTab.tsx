import { useEffect, useState } from "react";
import { ImageIcon } from "lucide-react";
import { Button, Input } from "@/components/ui";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import type { SettingsMap } from "@/types/settings";
import type { MediaItem } from "@/types/media";

interface BrandingTabProps {
  values: SettingsMap;
  isLoading: boolean;
  isSaving: boolean;
  onSave: (changed: Record<string, string>) => Promise<void>;
}

/**
 * UI/UX Design, Admin Sitemap — "Logo & Favicon" tab. First real
 * consumer of the reusable MediaPickerModal (Development Roadmap,
 * Milestone 1). Extended with independently adjustable display heights
 * for the logo's two public placements (header, footer) — same
 * uploaded asset, different natural size in each spot, so a single
 * "logo size" control wouldn't fit both without one of them looking
 * wrong.
 */
export function BrandingTab({ values, isLoading, isSaving, onSave }: BrandingTabProps) {
  const [pickerTarget, setPickerTarget] = useState<"logo_media_id" | "favicon_media_id" | null>(null);
  const [sizeDraft, setSizeDraft] = useState({ header_logo_height: "", footer_logo_height: "" });

  const logoId = values.logo_media_id;
  const faviconId = values.favicon_media_id;

  useEffect(() => {
    setSizeDraft({
      header_logo_height: values.header_logo_height ? String(values.header_logo_height) : "",
      footer_logo_height: values.footer_logo_height ? String(values.footer_logo_height) : "",
    });
  }, [values.header_logo_height, values.footer_logo_height]);

  async function handleSelect(item: MediaItem) {
    if (pickerTarget) {
      await onSave({ [pickerTarget]: String(item.id) });
    }
    setPickerTarget(null);
  }

  async function handleSaveSizes() {
    await onSave({
      header_logo_height: sizeDraft.header_logo_height,
      footer_logo_height: sizeDraft.footer_logo_height,
    });
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-6">
      {(
        [
          { key: "logo_media_id" as const, label: "Logo", value: logoId },
          { key: "favicon_media_id" as const, label: "Favicon", value: faviconId },
        ]
      ).map((field) => (
        <div key={field.key} className="flex items-center gap-4">
          <div className="flex h-16 w-16 items-center justify-center rounded-md border border-[color:var(--color-border)] bg-[color:var(--color-surface-alt)]">
            <ImageIcon className="h-6 w-6 text-neutral-400" aria-hidden="true" />
          </div>
          <div className="flex flex-col gap-1">
            <p className="text-body-sm font-medium text-[color:var(--color-text)]">{field.label}</p>
            <p className="text-caption text-neutral-500">{field.value ? `Media #${field.value}` : "Not set"}</p>
          </div>
          <Button
            variant="secondary"
            size="sm"
            className="ml-auto"
            isLoading={isSaving}
            onClick={() => setPickerTarget(field.key)}
          >
            Choose from Library
          </Button>
        </div>
      ))}

      <hr className="border-[color:var(--color-border)]" />

      <div className="flex flex-col gap-4">
        <div>
          <p className="text-body-sm font-medium text-[color:var(--color-text)]">Logo Display Size</p>
          <p className="text-caption text-neutral-500">
            Controls how tall the logo renders in each placement on the public site. The same logo image is used in
            both; only the display height differs.
          </p>
        </div>

        <div className="flex flex-wrap gap-4">
          <Input
            type="number"
            min={16}
            max={200}
            label="Header height (px)"
            value={sizeDraft.header_logo_height}
            onChange={(e) => setSizeDraft((d) => ({ ...d, header_logo_height: e.target.value }))}
            className="w-40"
          />
          <Input
            type="number"
            min={16}
            max={200}
            label="Footer height (px)"
            value={sizeDraft.footer_logo_height}
            onChange={(e) => setSizeDraft((d) => ({ ...d, footer_logo_height: e.target.value }))}
            className="w-40"
          />
        </div>

        <Button onClick={handleSaveSizes} isLoading={isSaving} className="self-start">
          Save Sizes
        </Button>
      </div>

      <MediaPickerModal
        open={pickerTarget !== null}
        onClose={() => setPickerTarget(null)}
        onSelect={handleSelect}
        type="image"
      />
    </div>
  );
}
