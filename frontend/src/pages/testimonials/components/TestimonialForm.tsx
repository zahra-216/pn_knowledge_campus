import { useEffect, useState } from "react";
import { Modal, Button, Input, Textarea, Switch } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import type { Testimonial, TestimonialPayload } from "@/types/homepage";

interface TestimonialFormProps {
  open: boolean;
  testimonial: Testimonial | null;
  onClose: () => void;
  onSave: (payload: TestimonialPayload) => Promise<void>;
}

const EMPTY: TestimonialPayload = { name: "", role_title: "", content: "", is_featured: false, is_active: true };

export function TestimonialForm({ open, testimonial, onClose, onSave }: TestimonialFormProps) {
  const [form, setForm] = useState<TestimonialPayload>(EMPTY);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      testimonial
        ? {
            name: testimonial.name,
            role_title: testimonial.role_title ?? "",
            content: testimonial.content,
            rating: testimonial.rating,
            is_featured: testimonial.is_featured,
            is_active: testimonial.is_active,
          }
        : EMPTY
    );
  }, [testimonial, open]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      onClose();
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={testimonial ? "Edit Testimonial" : "New Testimonial"}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={handleSave} isLoading={isSaving}>
            Save
          </Button>
        </>
      }
    >
      <div className="flex flex-col gap-4">
        <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
        <Input
          label="Role / Title"
          hint="e.g. 'BSc Graduate, 2024'"
          value={form.role_title ?? ""}
          onChange={(e) => setForm({ ...form, role_title: e.target.value })}
        />
        <Textarea label="Quote" value={form.content ?? ""} onChange={(e) => setForm({ ...form, content: e.target.value })} rows={4} required />

        <MediaIdField
          label="Photo"
          type="image"
          mediaId={null}
          previewUrl={testimonial?.photo_url}
          onChange={(id) => setForm({ ...form, media_id: id })}
        />

        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Rating (optional)</span>
          <select
            value={form.rating ?? ""}
            onChange={(e) => setForm({ ...form, rating: e.target.value ? Number(e.target.value) : null })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="">No rating</option>
            {[1, 2, 3, 4, 5].map((n) => (
              <option key={n} value={n}>
                {n} star{n > 1 ? "s" : ""}
              </option>
            ))}
          </select>
        </label>

        <Switch
          label="Featured on homepage"
          checked={form.is_featured ?? false}
          onChange={(checked) => setForm({ ...form, is_featured: checked })}
        />
        <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
      </div>
    </Modal>
  );
}
