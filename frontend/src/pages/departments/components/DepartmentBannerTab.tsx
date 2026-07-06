import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import type { Department, DepartmentPayload } from "@/types/department";

interface DepartmentBannerTabProps {
  department: Department;
  onSave: (payload: DepartmentPayload) => Promise<void>;
}

export function DepartmentBannerTab({ department, onSave }: DepartmentBannerTabProps) {
  return (
    <div className="flex flex-col gap-4">
      <MediaIdField
        label="Banner Image"
        type="image"
        mediaId={null}
        previewUrl={department.banner_url}
        onChange={(id) => onSave({ banner_media_id: id })}
      />
    </div>
  );
}
