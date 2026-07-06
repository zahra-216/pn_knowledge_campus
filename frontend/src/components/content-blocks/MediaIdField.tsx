import { useState } from "react";
import { ImagePlus, X } from "lucide-react";
import { Button } from "@/components/ui";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import type { MediaItem, MediaTypeFilter } from "@/types/media";

interface MediaIdFieldProps {
  label: string;
  mediaId: number | null | undefined;
  onChange: (mediaId: number | null) => void;
  type?: MediaTypeFilter;
  /**
   * A URL for an already-attached asset (e.g. HeroSlideResource's
   * thumb_url), shown until the admin picks a replacement in this
   * session. For fields that only ever hand back a raw id (Page Builder
   * blocks), omit this — see the id-only fallback below.
   */
  previewUrl?: string | null;
}

/**
 * Single Media Library asset selector, reused by every block/record
 * field that stores a `*_media_id` (hero background, image, video
 * upload, avatar, partner logo). Without a `previewUrl`, a saved id from
 * a previous session shows as a plain reference rather than a thumbnail
 * until it's re-picked — media/{id} has no single-item GET endpoint yet.
 */
export function MediaIdField({ label, mediaId, onChange, type, previewUrl }: MediaIdFieldProps) {
  const [pickerOpen, setPickerOpen] = useState(false);
  const [picked, setPicked] = useState<MediaItem | null>(null);

  const displayUrl = picked?.thumb_url ?? previewUrl;
  const hasMedia = Boolean(mediaId || displayUrl);

  return (
    <div className="flex flex-col gap-1.5">
      <span className="text-body-sm font-medium text-[color:var(--color-text)]">{label}</span>
      <div className="flex items-center gap-3">
        {displayUrl ? (
          <img src={displayUrl} alt={picked?.alt_text ?? ""} className="h-14 w-14 rounded-sm object-cover" />
        ) : mediaId ? (
          <span className="text-body-sm text-neutral-500">Media #{mediaId} selected</span>
        ) : (
          <span className="text-body-sm text-neutral-500">No media selected</span>
        )}
        <Button type="button" variant="secondary" onClick={() => setPickerOpen(true)}>
          <ImagePlus className="h-4 w-4" aria-hidden="true" />
          {hasMedia ? "Change" : "Select"}
        </Button>
        {hasMedia && (
          <button
            type="button"
            aria-label="Remove media"
            onClick={() => {
              setPicked(null);
              onChange(null);
            }}
            className="rounded p-1.5 text-neutral-500 hover:text-danger"
          >
            <X className="h-4 w-4" aria-hidden="true" />
          </button>
        )}
      </div>

      <MediaPickerModal
        open={pickerOpen}
        onClose={() => setPickerOpen(false)}
        type={type}
        onSelect={(item) => {
          setPicked(item);
          onChange(item.id);
        }}
      />
    </div>
  );
}
