import { useEffect, useState } from "react";
import { Button, Input, Textarea, useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import type { Faculty, FacultyPayload } from "@/types/faculty";

interface FacultyDeanTabProps {
  faculty: Faculty;
  canEdit: boolean;
  onSave: (payload: FacultyPayload) => Promise<void>;
}

export function FacultyDeanTab({ faculty, canEdit, onSave }: FacultyDeanTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<FacultyPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      dean_name: faculty.dean_name ?? "",
      dean_title: faculty.dean_title ?? "",
      dean_message: faculty.dean_message ?? "",
    });
  }, [faculty]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Dean details saved.", "success");
    } catch {
      showToast("Could not save Dean details.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Dean Name" value={form.dean_name ?? ""} onChange={(e) => setForm({ ...form, dean_name: e.target.value })} />
      <Input label="Dean Title" hint="e.g. 'Dean, Faculty of Business'" value={form.dean_title ?? ""} onChange={(e) => setForm({ ...form, dean_title: e.target.value })} />
      <Textarea
        label="Dean's Message"
        value={form.dean_message ?? ""}
        onChange={(e) => setForm({ ...form, dean_message: e.target.value })}
        rows={5}
      />

      <MediaIdField
        label="Dean Photo"
        type="image"
        mediaId={null}
        previewUrl={faculty.dean_photo_url}
        onChange={(id) => onSave({ dean_photo_media_id: id })}
      />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Dean Details
        </Button>
      )}
    </fieldset>
  );
}
