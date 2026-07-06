import { useEffect, useState } from "react";
import { Button, Input, Textarea, useToast } from "@/components/ui";
import type { Faculty, FacultyPayload, FacultyStatus } from "@/types/faculty";

interface FacultyDetailsTabProps {
  faculty: Faculty;
  canEdit: boolean;
  onSave: (payload: FacultyPayload) => Promise<void>;
}

export function FacultyDetailsTab({ faculty, canEdit, onSave }: FacultyDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<FacultyPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      name: faculty.name,
      slug: faculty.slug,
      short_description: faculty.short_description ?? "",
      description: faculty.description ?? "",
      order: faculty.order,
      status: faculty.status,
    });
  }, [faculty]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Faculty details saved.", "success");
    } catch {
      showToast("Could not save. Check the name and slug.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the name if left blank. Public URL: /faculties/{slug}."
        value={form.slug ?? ""}
        onChange={(e) => setForm({ ...form, slug: e.target.value })}
      />
      <Textarea
        label="Short Description"
        hint="Used in listing cards."
        value={form.short_description ?? ""}
        onChange={(e) => setForm({ ...form, short_description: e.target.value })}
        rows={2}
      />
      <Textarea
        label="Description"
        hint="Full body for the faculty detail page."
        value={form.description ?? ""}
        onChange={(e) => setForm({ ...form, description: e.target.value })}
        rows={8}
      />

      <div className="grid grid-cols-2 gap-3">
        <Input
          label="Order"
          type="number"
          hint="Lower numbers appear first."
          value={form.order ?? 0}
          onChange={(e) => setForm({ ...form, order: Number(e.target.value) })}
        />
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Status</span>
          <select
            value={form.status ?? "draft"}
            onChange={(e) => setForm({ ...form, status: e.target.value as FacultyStatus })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
          </select>
        </label>
      </div>

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Details
        </Button>
      )}
    </fieldset>
  );
}
