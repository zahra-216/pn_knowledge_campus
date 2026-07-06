import { useCallback, useEffect, useState } from "react";
import { Plus, Pencil, Trash2 } from "lucide-react";
import { Button, Modal, Input, Textarea, Switch, useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { CourseFaq, CourseFaqPayload } from "@/types/course";

interface CourseFaqsTabProps {
  courseId: number;
  canEdit: boolean;
}

export function CourseFaqsTab({ courseId, canEdit }: CourseFaqsTabProps) {
  const { showToast } = useToast();
  const [faqs, setFaqs] = useState<CourseFaq[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; faq: CourseFaq | null }>({ open: false, faq: null });
  const [question, setQuestion] = useState("");
  const [answer, setAnswer] = useState("");
  const [isActive, setIsActive] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  const fetchFaqs = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<CourseFaq[]>>(ENDPOINTS.courses.faqs(courseId));
      setFaqs(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [courseId]);

  useEffect(() => {
    fetchFaqs();
  }, [fetchFaqs]);

  function openForm(faq: CourseFaq | null) {
    setFormState({ open: true, faq });
    setQuestion(faq?.question ?? "");
    setAnswer(faq?.answer ?? "");
    setIsActive(faq?.is_active ?? true);
  }

  async function handleSave() {
    setIsSaving(true);
    const payload: CourseFaqPayload = { question, answer, is_active: isActive };
    try {
      if (formState.faq) {
        await api.put(ENDPOINTS.courses.faqs(courseId, formState.faq.id), payload);
      } else {
        await api.post(ENDPOINTS.courses.faqs(courseId), payload);
      }
      setFormState({ open: false, faq: null });
      await fetchFaqs();
    } catch {
      showToast("Could not save this FAQ.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(faq: CourseFaq) {
    await api.delete(ENDPOINTS.courses.faqs(courseId, faq.id));
    await fetchFaqs();
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {faqs.map((faq) => (
        <div key={faq.id} className="rounded-md border border-[color:var(--color-border)] p-3">
          <div className="flex items-start justify-between gap-3">
            <div className="flex flex-col gap-1">
              <span className="text-body-sm font-semibold text-[color:var(--color-text)]">{faq.question}</span>
              <span className="text-body-sm text-neutral-500">{faq.answer}</span>
              {!faq.is_active && <span className="text-caption text-neutral-400">Inactive</span>}
            </div>
            {canEdit && (
              <div className="flex flex-shrink-0 items-center gap-1">
                <button type="button" onClick={() => openForm(faq)} aria-label="Edit FAQ" className="rounded p-1.5 text-neutral-500 hover:bg-black/5">
                  <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => handleDelete(faq)} aria-label="Delete FAQ" className="rounded p-1.5 text-neutral-500 hover:text-danger">
                  <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              </div>
            )}
          </div>
        </div>
      ))}

      {faqs.length === 0 && <p className="text-body-sm text-neutral-500">No FAQs yet.</p>}

      {canEdit && (
        <Button variant="secondary" onClick={() => openForm(null)} className="self-start">
          <Plus className="h-4 w-4" aria-hidden="true" />
          Add FAQ
        </Button>
      )}

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, faq: null })}
        title={formState.faq ? "Edit FAQ" : "New FAQ"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, faq: null })}>
              Cancel
            </Button>
            <Button onClick={handleSave} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <Input label="Question" value={question} onChange={(e) => setQuestion(e.target.value)} required />
          <Textarea label="Answer" value={answer} onChange={(e) => setAnswer(e.target.value)} rows={4} required />
          <Switch label="Active" checked={isActive} onChange={setIsActive} />
        </div>
      </Modal>
    </div>
  );
}
