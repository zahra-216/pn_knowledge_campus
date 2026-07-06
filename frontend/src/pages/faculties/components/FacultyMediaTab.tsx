import { useState } from "react";
import { Plus, Trash2 } from "lucide-react";
import { useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { Faculty, FacultyPayload } from "@/types/faculty";
import type { MediaItem } from "@/types/media";

interface FacultyMediaTabProps {
  faculty: Faculty;
  canEdit: boolean;
  onSave: (payload: FacultyPayload) => Promise<void>;
  onRefresh: () => Promise<void>;
}

/**
 * Faculty Banner + icon (single-file, saved through the normal update
 * endpoint) and Faculty Gallery (multi-file, its own attach/detach
 * sub-resource — see FacultyController::attachGallery/detachGallery).
 */
export function FacultyMediaTab({ faculty, canEdit, onSave, onRefresh }: FacultyMediaTabProps) {
  const { showToast } = useToast();
  const [isGalleryPickerOpen, setIsGalleryPickerOpen] = useState(false);
  const [isAttaching, setIsAttaching] = useState(false);

  async function handleAttachGallery(item: MediaItem) {
    setIsAttaching(true);
    try {
      await api.post(ENDPOINTS.faculties.gallery(faculty.id), { media_ids: [item.id] });
      await onRefresh();
      showToast("Image added to gallery.", "success");
    } catch {
      showToast("Could not add this image.", "error");
    } finally {
      setIsAttaching(false);
      setIsGalleryPickerOpen(false);
    }
  }

  async function handleRemoveGalleryItem(mediaId: number) {
    await api.delete(ENDPOINTS.faculties.gallery(faculty.id, mediaId));
    await onRefresh();
  }

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Faculty Banner</h3>
        <MediaIdField
          label="Banner Image"
          type="image"
          mediaId={null}
          previewUrl={faculty.banner_url}
          onChange={(id) => onSave({ banner_media_id: id })}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Icon</h3>
        <MediaIdField label="Icon" type="image" mediaId={null} previewUrl={faculty.icon_url} onChange={(id) => onSave({ icon_media_id: id })} />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Faculty Gallery</h3>
        <div className="flex flex-wrap gap-3">
          {faculty.gallery.map((item) => (
            <div key={item.id} className="group relative h-24 w-24">
              <img src={item.thumb_url} alt="" className="h-24 w-24 rounded-md object-cover" />
              {canEdit && (
                <button
                  type="button"
                  aria-label="Remove from gallery"
                  onClick={() => handleRemoveGalleryItem(item.id)}
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
              onClick={() => setIsGalleryPickerOpen(true)}
              className="flex h-24 w-24 flex-col items-center justify-center gap-1 rounded-md border border-dashed border-[color:var(--color-border)] text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
            >
              <Plus className="h-5 w-5" aria-hidden="true" />
              <span className="text-caption">Add Image</span>
            </button>
          )}
        </div>

        {faculty.gallery.length === 0 && <p className="text-body-sm text-neutral-500">No gallery images yet.</p>}
      </section>

      <MediaPickerModal open={isGalleryPickerOpen} onClose={() => setIsGalleryPickerOpen(false)} onSelect={handleAttachGallery} type="image" />
      {isAttaching && <p className="text-caption text-neutral-500">Adding image...</p>}
    </div>
  );
}
