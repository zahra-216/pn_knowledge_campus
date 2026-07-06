import { useEffect, useState } from "react";
import { Modal, Button, Input, Textarea, Switch } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection } from "@/types/api";
import type { Faq, FaqCategory, FaqPayload } from "@/types/faq";

interface FaqFormProps {
  open: boolean;
  faq: Faq | null;
  onClose: () => void;
  onSave: (payload: FaqPayload) => Promise<void>;
}

const EMPTY: FaqPayload = { question: "", answer: "", category_id: null, order: 0, is_active: true };

export function FaqForm({ open, faq, onClose, onSave }: FaqFormProps) {
  const [form, setForm] = useState<FaqPayload>(EMPTY);
  const [categories, setCategories] = useState<FaqCategory[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      faq
        ? {
            question: faq.question,
            answer: faq.answer,
            category_id: faq.category?.id ?? null,
            order: faq.order,
            is_active: faq.is_active,
          }
        : EMPTY
    );
  }, [faq, open]);

  useEffect(() => {
    if (!open) return;
    api
      .get<ApiCollection<FaqCategory>>(ENDPOINTS.faqCategories.admin(), { params: { per_page: 100 } })
      .then(({ data }) => setCategories(data.data));
  }, [open]);

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
      title={faq ? "Edit FAQ" : "New FAQ"}
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
        <Input label="Question" value={form.question ?? ""} onChange={(e) => setForm({ ...form, question: e.target.value })} required />
        <Textarea label="Answer" rows={5} value={form.answer ?? ""} onChange={(e) => setForm({ ...form, answer: e.target.value })} required />

        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Category (optional)</span>
          <select
            value={form.category_id ?? ""}
            onChange={(e) => setForm({ ...form, category_id: e.target.value ? Number(e.target.value) : null })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="">No category</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </label>

        <Input
          label="Order"
          type="number"
          hint="Lower numbers appear first."
          value={form.order ?? 0}
          onChange={(e) => setForm({ ...form, order: Number(e.target.value) })}
        />

        <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
      </div>
    </Modal>
  );
}
