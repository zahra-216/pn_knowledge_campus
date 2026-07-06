import { useState } from "react";
import { Plus, Trash2 } from "lucide-react";
import { useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { CampusEvent, EventPayload } from "@/types/event";
import type { MediaItem } from "@/types/media";

interface EventMediaTabProps {
  event: CampusEvent;
  canEdit: boolean;
  onSave: (payload: EventPayload) => Promise<void>;
  onRefresh: () => Promise<void>;
}

/**
 * Featured Image (single-file, saved through the normal update endpoint)
 * and Gallery (multi-file, its own attach/detach sub-resource — see
 * EventController::attachMedia/detachMedia). Mirrors
 * BlogPostMediaTab/NewsMediaTab's same pattern.
 */
export function EventMediaTab({ event, canEdit, onSave, onRefresh }: EventMediaTabProps) {
  const { showToast } = useToast();
  const [isPickerOpen, setIsPickerOpen] = useState(false);

  async function handleAttach(item: MediaItem) {
    try {
      await api.post(ENDPOINTS.events.media(event.id), { media_ids: [item.id] });
      await onRefresh();
      showToast("Image added to gallery.", "success");
    } catch {
      showToast("Could not add this image.", "error");
    } finally {
      setIsPickerOpen(false);
    }
  }

  async function handleRemove(mediaId: number) {
    await api.delete(ENDPOINTS.events.detachMedia(event.id, mediaId));
    await onRefresh();
  }

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Featured Image</h3>
        <MediaIdField
          label="Featured Image"
          type="image"
          mediaId={null}
          previewUrl={event.featured_image_url}
          onChange={(id) => onSave({ featured_image_media_id: id })}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Gallery</h3>
        <div className="flex flex-wrap gap-3">
          {event.gallery.map((item) => (
            <div key={item.id} className="group relative h-24 w-24">
              <img src={item.thumb_url} alt="" className="h-24 w-24 rounded-md object-cover" />
              {canEdit && (
                <button
                  type="button"
                  aria-label="Remove from gallery"
                  onClick={() => handleRemove(item.id)}
                  className="absolute right-1 top-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100"
                >
                  <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              )}
            </div>
          ))}
          {canEdit && (
            <button
              type="button"
              onClick={() => setIsPickerOpen(true)}
              className="flex h-24 w-24 flex-col items-center justify-center gap-1 rounded-md border border-dashed border-[color:var(--color-border)] text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
            >
              <Plus className="h-5 w-5" aria-hidden="true" />
              <span className="text-caption">Add Image</span>
            </button>
          )}
        </div>
        {event.gallery.length === 0 && <p className="text-body-sm text-neutral-500">No gallery images yet.</p>}
      </section>

      <MediaPickerModal open={isPickerOpen} onClose={() => setIsPickerOpen(false)} onSelect={handleAttach} type="image" />
    </div>
  );
}
