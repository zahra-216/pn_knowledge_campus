import { useState } from "react";
import { Plus, Trash2, FileText } from "lucide-react";
import { useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { Course, CoursePayload } from "@/types/course";
import type { MediaItem } from "@/types/media";

interface CourseMediaTabProps {
  course: Course;
  canEdit: boolean;
  onSave: (payload: CoursePayload) => Promise<void>;
  onRefresh: () => Promise<void>;
}

/**
 * Featured Image (single, saved through the normal update endpoint) and
 * Gallery/Downloads (multi-file, their own attach/detach sub-resource —
 * see CourseController::attachMedia/detachMedia). Mirrors
 * FacultyMediaTab's gallery pattern, extended with a second
 * document-typed collection for Downloads.
 */
export function CourseMediaTab({ course, canEdit, onSave, onRefresh }: CourseMediaTabProps) {
  const { showToast } = useToast();
  const [pickerFor, setPickerFor] = useState<"gallery" | "downloads" | null>(null);

  async function handleAttach(item: MediaItem) {
    if (!pickerFor) return;
    try {
      await api.post(ENDPOINTS.courses.media(course.id), { media_ids: [item.id], collection: pickerFor });
      await onRefresh();
      showToast("Media added.", "success");
    } catch {
      showToast("Could not add this file.", "error");
    } finally {
      setPickerFor(null);
    }
  }

  async function handleRemove(mediaId: number, collection: "gallery" | "downloads") {
    await api.delete(`${ENDPOINTS.courses.detachMedia(course.id, mediaId)}?collection=${collection}`);
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
          previewUrl={course.featured_image_url}
          onChange={(id) => onSave({ featured_image_media_id: id })}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Gallery</h3>
        <div className="flex flex-wrap gap-3">
          {course.gallery.map((item) => (
            <div key={item.id} className="group relative h-24 w-24">
              <img src={item.thumb_url} alt="" className="h-24 w-24 rounded-md object-cover" />
              {canEdit && (
                <button
                  type="button"
                  aria-label="Remove from gallery"
                  onClick={() => handleRemove(item.id, "gallery")}
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
              onClick={() => setPickerFor("gallery")}
              className="flex h-24 w-24 flex-col items-center justify-center gap-1 rounded-md border border-dashed border-[color:var(--color-border)] text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
            >
              <Plus className="h-5 w-5" aria-hidden="true" />
              <span className="text-caption">Add Image</span>
            </button>
          )}
        </div>
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Downloads</h3>
        <ul className="flex flex-col gap-2">
          {course.downloads.map((file) => (
            <li key={file.id} className="flex items-center gap-2 rounded-md border border-[color:var(--color-border)] px-3 py-2">
              <FileText className="h-4 w-4 flex-shrink-0 text-neutral-500" aria-hidden="true" />
              <a href={file.url} target="_blank" rel="noreferrer" className="flex-1 truncate text-body-sm text-navy hover:underline">
                {file.name}
              </a>
              {canEdit && (
                <button type="button" onClick={() => handleRemove(file.id, "downloads")} aria-label={`Remove ${file.name}`}>
                  <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
                </button>
              )}
            </li>
          ))}
        </ul>
        {course.downloads.length === 0 && <p className="text-body-sm text-neutral-500">No downloadable files yet.</p>}
        {canEdit && (
          <button
            type="button"
            onClick={() => setPickerFor("downloads")}
            className="flex items-center gap-2 self-start rounded-md border border-dashed border-[color:var(--color-border)] px-3 py-2 text-body-sm text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
          >
            <Plus className="h-4 w-4" aria-hidden="true" />
            Add File
          </button>
        )}
      </section>

      <MediaPickerModal
        open={pickerFor !== null}
        onClose={() => setPickerFor(null)}
        onSelect={handleAttach}
        type={pickerFor === "downloads" ? "document" : "image"}
      />
    </div>
  );
}
