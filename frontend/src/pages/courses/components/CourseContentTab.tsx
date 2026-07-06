import { useEffect, useState } from "react";
import { Button, Textarea, useToast } from "@/components/ui";
import type { Course, CoursePayload } from "@/types/course";

interface CourseContentTabProps {
  course: Course;
  canEdit: boolean;
  onSave: (payload: CoursePayload) => Promise<void>;
}

export function CourseContentTab({ course, canEdit, onSave }: CourseContentTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<CoursePayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      overview: course.overview,
      description: course.description,
      entry_requirements: course.entry_requirements ?? "",
      learning_outcomes: course.learning_outcomes ?? "",
      career_opportunities: course.career_opportunities ?? "",
    });
  }, [course]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Course content saved.", "success");
    } catch {
      showToast("Could not save course content.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Textarea
        label="Overview"
        hint="Short summary shown on listing cards."
        value={form.overview ?? ""}
        onChange={(e) => setForm({ ...form, overview: e.target.value })}
        rows={2}
        required
      />
      <Textarea
        label="Description"
        hint="Full body — supports HTML/rich text."
        value={form.description ?? ""}
        onChange={(e) => setForm({ ...form, description: e.target.value })}
        rows={8}
        required
      />
      <Textarea
        label="Entry Requirements"
        value={form.entry_requirements ?? ""}
        onChange={(e) => setForm({ ...form, entry_requirements: e.target.value })}
        rows={4}
      />
      <Textarea
        label="Learning Outcomes"
        value={form.learning_outcomes ?? ""}
        onChange={(e) => setForm({ ...form, learning_outcomes: e.target.value })}
        rows={4}
      />
      <Textarea
        label="Career Opportunities"
        value={form.career_opportunities ?? ""}
        onChange={(e) => setForm({ ...form, career_opportunities: e.target.value })}
        rows={4}
      />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Content
        </Button>
      )}
    </fieldset>
  );
}
