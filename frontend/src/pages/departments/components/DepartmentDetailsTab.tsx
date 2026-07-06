import { useEffect, useState } from "react";
import { Button, Input, Textarea, useToast } from "@/components/ui";
import type { Department, DepartmentPayload, DepartmentStatus } from "@/types/department";
import type { Faculty } from "@/types/faculty";

interface DepartmentDetailsTabProps {
  department: Department;
  faculties: Faculty[];
  canEdit: boolean;
  onSave: (payload: DepartmentPayload) => Promise<void>;
}

export function DepartmentDetailsTab({ department, faculties, canEdit, onSave }: DepartmentDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<DepartmentPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      faculty_id: department.faculty_id,
      name: department.name,
      slug: department.slug,
      short_description: department.short_description ?? "",
      description: department.description ?? "",
      order: department.order,
      status: department.status,
    });
  }, [department]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Department details saved.", "success");
    } catch {
      showToast("Could not save. Check the name, slug, and faculty.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <label className="flex flex-col gap-1.5">
        <span className="text-body-sm font-medium text-[color:var(--color-text)]">Faculty</span>
        <select
          value={form.faculty_id ?? ""}
          onChange={(e) => setForm({ ...form, faculty_id: Number(e.target.value) })}
          className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          required
        >
          {faculties.map((faculty) => (
            <option key={faculty.id} value={faculty.id}>
              {faculty.name}
            </option>
          ))}
        </select>
      </label>

      <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the name if left blank. Public URL: /departments/{slug}."
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
        hint="Full body for the department detail page."
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
            onChange={(e) => setForm({ ...form, status: e.target.value as DepartmentStatus })}
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
