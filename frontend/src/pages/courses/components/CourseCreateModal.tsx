import { useEffect, useState } from "react";
import { Modal, Button, Input, Textarea, useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { Course, CourseLookup, CoursePayload } from "@/types/course";
import type { Faculty } from "@/types/faculty";
import type { Department } from "@/types/department";

interface CourseCreateModalProps {
  open: boolean;
  onClose: () => void;
  onCreated: (course: Course) => void;
}

const EMPTY: CoursePayload = {
  course_name: "",
  course_code: "",
  duration_value: 1,
  duration_unit: "year",
  overview: "",
  description: "",
};

/**
 * Course has too many required fields (Faculty, Department, Level, Mode,
 * Name, Code, Duration, Overview, Description) for the "create a bare
 * stub then edit" pattern Faculty/Department use — this collects the
 * minimum upfront, then the full editor handles everything else
 * (Curriculum, Media, FAQs, SEO, entry requirements, etc.).
 */
export function CourseCreateModal({ open, onClose, onCreated }: CourseCreateModalProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<CoursePayload>(EMPTY);
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [levels, setLevels] = useState<CourseLookup[]>([]);
  const [modes, setModes] = useState<CourseLookup[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (!open) return;
    setForm(EMPTY);

    Promise.all([
      api.get<ApiCollection<Faculty>>(ENDPOINTS.faculties.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<Department>>(ENDPOINTS.departments.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<CourseLookup>>(ENDPOINTS.courseLevels.admin(), { params: { per_page: 100 } }),
      api.get<ApiCollection<CourseLookup>>(ENDPOINTS.courseModes.admin(), { params: { per_page: 100 } }),
    ]).then(([facultiesRes, departmentsRes, levelsRes, modesRes]) => {
      setFaculties(facultiesRes.data.data);
      setDepartments(departmentsRes.data.data);
      setLevels(levelsRes.data.data);
      setModes(modesRes.data.data);
    });
  }, [open]);

  const departmentsForFaculty = departments.filter((d) => d.faculty_id === form.faculty_id);

  async function handleSave() {
    setIsSaving(true);
    try {
      const { data } = await api.post<ApiResponse<Course>>(ENDPOINTS.courses.admin(), form);
      onCreated(data.data);
    } catch {
      showToast("Could not create this course. Check all required fields.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  const missingLookups = faculties.length === 0 || departments.length === 0 || levels.length === 0 || modes.length === 0;

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="New Course"
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={handleSave} isLoading={isSaving} disabled={missingLookups}>
            Create
          </Button>
        </>
      }
    >
      {missingLookups ? (
        <p className="text-body-sm text-neutral-500">
          Set up at least one Faculty, Department, Course Level, and Course Mode before creating a course.
        </p>
      ) : (
        <div className="flex flex-col gap-4">
          <Input label="Course Name" value={form.course_name ?? ""} onChange={(e) => setForm({ ...form, course_name: e.target.value })} required />
          <Input label="Course Code" value={form.course_code ?? ""} onChange={(e) => setForm({ ...form, course_code: e.target.value })} required />

          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Faculty</span>
            <select
              value={form.faculty_id ?? ""}
              onChange={(e) => setForm({ ...form, faculty_id: Number(e.target.value), department_id: undefined })}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="">Select a faculty</option>
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
              disabled={!form.faculty_id}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body disabled:opacity-50"
            >
              <option value="">Select a department</option>
              {departmentsForFaculty.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.name}
                </option>
              ))}
            </select>
          </label>

          <div className="grid grid-cols-2 gap-3">
            <label className="flex flex-col gap-1.5">
              <span className="text-body-sm font-medium text-[color:var(--color-text)]">Level</span>
              <select
                value={form.level_id ?? ""}
                onChange={(e) => setForm({ ...form, level_id: Number(e.target.value) })}
                className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
              >
                <option value="">Select a level</option>
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
                <option value="">Select a mode</option>
                {modes.map((m) => (
                  <option key={m.id} value={m.id}>
                    {m.name}
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

          <Textarea
            label="Overview"
            hint="Short summary for listing cards."
            value={form.overview ?? ""}
            onChange={(e) => setForm({ ...form, overview: e.target.value })}
            rows={2}
          />
          <Textarea label="Description" value={form.description ?? ""} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={4} />
        </div>
      )}
    </Modal>
  );
}
