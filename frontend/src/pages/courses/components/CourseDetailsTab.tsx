import { useEffect, useState } from "react";
import { Button, Input, Switch, useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { Course, CoursePayload, CourseStatus, CourseLookup, CourseCategory } from "@/types/course";
import type { Faculty } from "@/types/faculty";
import type { Department } from "@/types/department";

interface CourseDetailsTabProps {
  course: Course;
  canEdit: boolean;
  onSave: (payload: CoursePayload) => Promise<void>;
}

/** Flattens the category list into a depth-first, indented option order (Category > Subcategory). */
function flattenCategories(categories: CourseCategory[], parentId: number | null = null, depth = 0): { id: number; label: string }[] {
  return categories
    .filter((c) => c.parent_id === parentId)
    .sort((a, b) => a.order - b.order)
    .flatMap((c) => [{ id: c.id, label: `${"— ".repeat(depth)}${c.name}` }, ...flattenCategories(categories, c.id, depth + 1)]);
}

export function CourseDetailsTab({ course, canEdit, onSave }: CourseDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<CoursePayload>({});
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [levels, setLevels] = useState<CourseLookup[]>([]);
  const [modes, setModes] = useState<CourseLookup[]>([]);
  const [categories, setCategories] = useState<CourseCategory[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      course_name: course.course_name,
      course_code: course.course_code,
      slug: course.slug,
      faculty_id: course.faculty.id,
      department_id: course.department.id,
      level_id: course.level.id,
      mode_id: course.mode.id,
      category_id: course.category?.id ?? null,
      duration_value: course.duration_value,
      duration_unit: course.duration_unit,
      price: course.price.amount,
      discount_price: course.price.discount_amount,
      price_currency: course.price.currency,
      status: course.status,
      published_at: course.published_at,
      is_featured: course.is_featured,
      order: course.order,
    });

    Promise.all([
      api.get<ApiCollection<Faculty>>(ENDPOINTS.faculties.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<Department>>(ENDPOINTS.departments.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<CourseLookup>>(ENDPOINTS.courseLevels.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<CourseLookup>>(ENDPOINTS.courseModes.admin(), { params: { per_page: 100 } }),
      api.get<ApiResponse<CourseCategory[]>>(ENDPOINTS.courseCategories.admin()),
    ]).then(([f, d, l, m, c]) => {
      setFaculties(f.data.data);
      setDepartments(d.data.data);
      setLevels(l.data.data);
      setModes(m.data.data);
      setCategories(c.data.data);
    });
  }, [course]);

  const departmentsForFaculty = departments.filter((d) => d.faculty_id === form.faculty_id);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Course details saved.", "success");
    } catch {
      showToast("Could not save. Check required fields and uniqueness of code/slug.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <div className="grid grid-cols-2 gap-3">
        <Input label="Course Name" value={form.course_name ?? ""} onChange={(e) => setForm({ ...form, course_name: e.target.value })} required />
        <Input label="Course Code" value={form.course_code ?? ""} onChange={(e) => setForm({ ...form, course_code: e.target.value })} required />
      </div>

      <Input label="Slug" hint="Public URL: /courses/{slug}." value={form.slug ?? ""} onChange={(e) => setForm({ ...form, slug: e.target.value })} />

      <div className="grid grid-cols-2 gap-3">
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Faculty</span>
          <select
            value={form.faculty_id ?? ""}
            onChange={(e) => setForm({ ...form, faculty_id: Number(e.target.value), department_id: undefined })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            {faculties.map((f) => (
              <option key={f.id} value={f.id}>
                {f.name}
              </option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Department</span>
          <select
            value={form.department_id ?? ""}
            onChange={(e) => setForm({ ...form, department_id: Number(e.target.value) })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            {departmentsForFaculty.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name}
              </option>
            ))}
          </select>
        </label>
      </div>

      <div className="grid grid-cols-3 gap-3">
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Level</span>
          <select
            value={form.level_id ?? ""}
            onChange={(e) => setForm({ ...form, level_id: Number(e.target.value) })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            {levels.map((l) => (
              <option key={l.id} value={l.id}>
                {l.name}
              </option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Mode</span>
          <select
            value={form.mode_id ?? ""}
            onChange={(e) => setForm({ ...form, mode_id: Number(e.target.value) })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            {modes.map((m) => (
              <option key={m.id} value={m.id}>
                {m.name}
              </option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Category (optional)</span>
          <select
            value={form.category_id ?? ""}
            onChange={(e) => setForm({ ...form, category_id: e.target.value ? Number(e.target.value) : null })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="">None</option>
            {flattenCategories(categories).map((c) => (
              <option key={c.id} value={c.id}>
                {c.label}
              </option>
            ))}
          </select>
        </label>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <Input
          label="Duration"
          type="number"
          value={form.duration_value ?? 1}
          onChange={(e) => setForm({ ...form, duration_value: Number(e.target.value) })}
        />
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Duration Unit</span>
          <select
            value={form.duration_unit ?? "year"}
            onChange={(e) => setForm({ ...form, duration_unit: e.target.value as CoursePayload["duration_unit"] })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="day">Day(s)</option>
            <option value="week">Week(s)</option>
            <option value="month">Month(s)</option>
            <option value="year">Year(s)</option>
          </select>
        </label>
      </div>

      <div className="grid grid-cols-3 gap-3">
        <Input
          label="Price"
          type="number"
          value={form.price ?? ""}
          onChange={(e) => setForm({ ...form, price: e.target.value ? Number(e.target.value) : null })}
        />
        <Input
          label="Discount Price"
          hint="Must be lower than Price."
          type="number"
          value={form.discount_price ?? ""}
          onChange={(e) => setForm({ ...form, discount_price: e.target.value ? Number(e.target.value) : null })}
        />
        <Input
          label="Currency"
          hint="ISO 4217, e.g. LKR."
          value={form.price_currency ?? "LKR"}
          onChange={(e) => setForm({ ...form, price_currency: e.target.value })}
        />
      </div>

      <div className="grid grid-cols-2 gap-3">
        <Input label="Order" type="number" value={form.order ?? 0} onChange={(e) => setForm({ ...form, order: Number(e.target.value) })} />
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Status</span>
          <select
            value={form.status ?? "draft"}
            onChange={(e) => setForm({ ...form, status: e.target.value as CourseStatus })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="scheduled">Scheduled</option>
            <option value="archived">Archived</option>
          </select>
        </label>
      </div>

      <Input
        label="Publish Date/Time"
        type="datetime-local"
        hint="Required for Scheduled — the course goes live automatically at this time."
        value={form.published_at ? form.published_at.slice(0, 16) : ""}
        onChange={(e) => setForm({ ...form, published_at: e.target.value ? new Date(e.target.value).toISOString() : null })}
      />

      <Switch label="Featured on homepage" checked={form.is_featured ?? false} onChange={(checked) => setForm({ ...form, is_featured: checked })} />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Details
        </Button>
      )}
    </fieldset>
  );
}
