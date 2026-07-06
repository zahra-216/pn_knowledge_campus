import { useEffect, useState } from "react";
import { Button, Input, useToast } from "@/components/ui";
import type { CourseCategory, CourseCategoryPayload } from "@/types/course";

interface CourseCategoryDetailsTabProps {
  category: CourseCategory;
  allCategories: CourseCategory[];
  canEdit: boolean;
  onSave: (payload: CourseCategoryPayload) => Promise<void>;
}

/** Collects this category's own id plus every descendant's id, so it (and its subtree) can be excluded from the Parent select. */
function collectSelfAndDescendants(categories: CourseCategory[], rootId: number): Set<number> {
  const ids = new Set<number>([rootId]);
  let grew = true;

  while (grew) {
    grew = false;
    for (const c of categories) {
      if (c.parent_id !== null && ids.has(c.parent_id) && !ids.has(c.id)) {
        ids.add(c.id);
        grew = true;
      }
    }
  }

  return ids;
}

export function CourseCategoryDetailsTab({ category, allCategories, canEdit, onSave }: CourseCategoryDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<CourseCategoryPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      name: category.name,
      slug: category.slug,
      parent_id: category.parent_id,
      order: category.order,
    });
  }, [category]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Category details saved.", "success");
    } catch {
      showToast("Could not save. Check the name/slug are unique and the parent doesn't create a cycle.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  const excluded = collectSelfAndDescendants(allCategories, category.id);
  const parentOptions = allCategories.filter((c) => !excluded.has(c.id));

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the name if left blank."
        value={form.slug ?? ""}
        onChange={(e) => setForm({ ...form, slug: e.target.value })}
      />

      <label className="flex flex-col gap-1.5">
        <span className="text-body-sm font-medium text-[color:var(--color-text)]">Parent Category</span>
        <select
          value={form.parent_id ?? ""}
          onChange={(e) => setForm({ ...form, parent_id: e.target.value ? Number(e.target.value) : null })}
          className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
        >
          <option value="">None — top-level category</option>
          {parentOptions.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
      </label>

      <Input
        label="Order"
        type="number"
        hint="Lower numbers appear first among siblings."
        value={form.order ?? 0}
        onChange={(e) => setForm({ ...form, order: Number(e.target.value) })}
      />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Details
        </Button>
      )}
    </fieldset>
  );
}
