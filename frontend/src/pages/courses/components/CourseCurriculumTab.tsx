import { useCallback, useEffect, useState } from "react";
import { ChevronDown, ChevronUp, Plus, Trash2, Pencil } from "lucide-react";
import { Button, Modal, Input, Textarea, useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { CourseCurriculumItem, CourseCurriculumItemPayload } from "@/types/course";

interface CourseCurriculumTabProps {
  courseId: number;
  canEdit: boolean;
}

/**
 * Curriculum only nests two levels deep (module → lesson, Database
 * Design, Section 4.3), so this is simpler than Menu/Page Builder's
 * arbitrary-depth trees: Move Up/Down within a module's own lessons or
 * within the top-level modules, no drag-and-drop or indent/outdent
 * needed.
 */
export function CourseCurriculumTab({ courseId, canEdit }: CourseCurriculumTabProps) {
  const { showToast } = useToast();
  const [modules, setModules] = useState<CourseCurriculumItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; item: CourseCurriculumItem | null; parentId: number | null }>({
    open: false,
    item: null,
    parentId: null,
  });
  const [formTitle, setFormTitle] = useState("");
  const [formDescription, setFormDescription] = useState("");
  const [formDuration, setFormDuration] = useState("");
  const [isSaving, setIsSaving] = useState(false);

  const fetchCurriculum = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<CourseCurriculumItem[]>>(ENDPOINTS.courses.curriculum(courseId));
      setModules(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [courseId]);

  useEffect(() => {
    fetchCurriculum();
  }, [fetchCurriculum]);

  function openForm(item: CourseCurriculumItem | null, parentId: number | null) {
    setFormState({ open: true, item, parentId });
    setFormTitle(item?.title ?? "");
    setFormDescription(item?.description ?? "");
    setFormDuration(item?.duration ?? "");
  }

  async function handleSaveItem() {
    setIsSaving(true);
    const payload: CourseCurriculumItemPayload = { title: formTitle, description: formDescription || null, duration: formDuration || null };
    try {
      if (formState.item) {
        await api.put(ENDPOINTS.courses.curriculum(courseId, formState.item.id), payload);
      } else {
        await api.post(ENDPOINTS.courses.curriculum(courseId), { ...payload, parent_id: formState.parentId });
      }
      setFormState({ open: false, item: null, parentId: null });
      await fetchCurriculum();
    } catch {
      showToast("Could not save this item.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(item: CourseCurriculumItem) {
    await api.delete(ENDPOINTS.courses.curriculum(courseId, item.id));
    await fetchCurriculum();
  }

  async function reorderPair(a: { id: number; parent_id: number | null; order: number }, b: { id: number; parent_id: number | null; order: number }) {
    try {
      await api.patch(ENDPOINTS.courses.reorderCurriculum(courseId), {
        items: [
          { id: a.id, parent_id: a.parent_id, order: b.order },
          { id: b.id, parent_id: b.parent_id, order: a.order },
        ],
      });
      await fetchCurriculum();
    } catch {
      showToast("Could not reorder.", "error");
    }
  }

  function moveModule(index: number, direction: "up" | "down") {
    const target = direction === "up" ? index - 1 : index + 1;
    if (target < 0 || target >= modules.length) return;
    reorderPair(
      { id: modules[index].id, parent_id: null, order: modules[index].order },
      { id: modules[target].id, parent_id: null, order: modules[target].order }
    );
  }

  function moveLesson(moduleIndex: number, lessonIndex: number, direction: "up" | "down") {
    const lessons = modules[moduleIndex].children;
    const target = direction === "up" ? lessonIndex - 1 : lessonIndex + 1;
    if (target < 0 || target >= lessons.length) return;
    reorderPair(
      { id: lessons[lessonIndex].id, parent_id: modules[moduleIndex].id, order: lessons[lessonIndex].order },
      { id: lessons[target].id, parent_id: modules[moduleIndex].id, order: lessons[target].order }
    );
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {modules.map((module, moduleIndex) => (
        <div key={module.id} className="rounded-md border border-[color:var(--color-border)]">
          <div className="flex items-center gap-2 border-b border-[color:var(--color-border)] bg-[color:var(--color-surface-alt)] px-3 py-2">
            <span className="flex-1 text-body-sm font-semibold text-[color:var(--color-text)]">{module.title}</span>
            {canEdit && (
              <div className="flex items-center gap-0.5">
                <button type="button" onClick={() => moveModule(moduleIndex, "up")} disabled={moduleIndex === 0} aria-label="Move module up" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30">
                  <ChevronUp className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => moveModule(moduleIndex, "down")} disabled={moduleIndex === modules.length - 1} aria-label="Move module down" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30">
                  <ChevronDown className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => openForm(null, module.id)} aria-label="Add lesson" className="rounded p-1.5 text-neutral-500 hover:bg-black/5">
                  <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => openForm(module, null)} aria-label="Edit module" className="rounded p-1.5 text-neutral-500 hover:bg-black/5">
                  <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => handleDelete(module)} aria-label="Delete module" className="rounded p-1.5 text-neutral-500 hover:text-danger">
                  <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              </div>
            )}
          </div>

          <ul className="flex flex-col gap-1 p-3">
            {module.children.length === 0 && <li className="text-caption text-neutral-500">No lessons yet.</li>}
            {module.children.map((lesson, lessonIndex) => (
              <li key={lesson.id} className="flex items-center gap-2 rounded-sm px-2 py-1.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
                <span className="flex-1 text-body-sm text-[color:var(--color-text)]">{lesson.title}</span>
                {lesson.duration && <span className="text-caption text-neutral-500">{lesson.duration}</span>}
                {canEdit && (
                  <div className="flex items-center gap-0.5">
                    <button type="button" onClick={() => moveLesson(moduleIndex, lessonIndex, "up")} disabled={lessonIndex === 0} aria-label="Move lesson up" className="rounded p-1 text-neutral-500 hover:bg-black/5 disabled:opacity-30">
                      <ChevronUp className="h-3 w-3" aria-hidden="true" />
                    </button>
                    <button type="button" onClick={() => moveLesson(moduleIndex, lessonIndex, "down")} disabled={lessonIndex === module.children.length - 1} aria-label="Move lesson down" className="rounded p-1 text-neutral-500 hover:bg-black/5 disabled:opacity-30">
                      <ChevronDown className="h-3 w-3" aria-hidden="true" />
                    </button>
                    <button type="button" onClick={() => openForm(lesson, module.id)} aria-label="Edit lesson" className="rounded p-1 text-neutral-500 hover:bg-black/5">
                      <Pencil className="h-3 w-3" aria-hidden="true" />
                    </button>
                    <button type="button" onClick={() => handleDelete(lesson)} aria-label="Delete lesson" className="rounded p-1 text-neutral-500 hover:text-danger">
                      <Trash2 className="h-3 w-3" aria-hidden="true" />
                    </button>
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      ))}

      {canEdit && (
        <Button variant="secondary" onClick={() => openForm(null, null)} className="self-start">
          <Plus className="h-4 w-4" aria-hidden="true" />
          Add Module
        </Button>
      )}

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, item: null, parentId: null })}
        title={formState.item ? "Edit Item" : formState.parentId ? "New Lesson" : "New Module"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, item: null, parentId: null })}>
              Cancel
            </Button>
            <Button onClick={handleSaveItem} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <Input label="Title" value={formTitle} onChange={(e) => setFormTitle(e.target.value)} required />
          <Input label="Duration" hint="e.g. '12 weeks'" value={formDuration} onChange={(e) => setFormDuration(e.target.value)} />
          <Textarea label="Description" value={formDescription} onChange={(e) => setFormDescription(e.target.value)} rows={3} />
        </div>
      </Modal>
    </div>
  );
}
