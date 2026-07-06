import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import type { CourseCategory, CourseCategoryPayload } from "@/types/course";

interface CourseCategoryMediaTabProps {
  category: CourseCategory;
  canEdit: boolean;
  onSave: (payload: CourseCategoryPayload) => Promise<void>;
}

/** Icon + Image are both single-file, saved through the normal update endpoint (same pattern as Faculty's icon/banner). */
export function CourseCategoryMediaTab({ category, canEdit, onSave }: CourseCategoryMediaTabProps) {
  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Icon</h3>
        <MediaIdField
          label="Icon"
          type="image"
          mediaId={null}
          previewUrl={category.icon_url}
          onChange={(id) => onSave({ icon_media_id: id })}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Image</h3>
        <MediaIdField
          label="Image"
          type="image"
          mediaId={null}
          previewUrl={category.image_url}
          onChange={(id) => onSave({ image_media_id: id })}
        />
      </section>
      {!canEdit && <p className="text-caption text-neutral-500">You don't have permission to edit media.</p>}
    </div>
  );
}
