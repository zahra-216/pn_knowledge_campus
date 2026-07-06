import { useState, type FormEvent } from "react";
import { Modal } from "@/components/ui/Modal";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Textarea } from "@/components/ui/Textarea";
import { useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiError, ApiResponse } from "@/types/api";
import type { InquiryConfirmation, InquiryPayload } from "@/types/inquiry";

interface EnquireNowModalProps {
  open: boolean;
  onClose: () => void;
  /** Pre-fills the enquiry as course-specific (Course Detail's "Enquire Now" flow, FR-05). */
  courseId?: number;
  courseName?: string;
}

const EMPTY_FORM = { name: "", email: "", phone: "", message: "" };

/**
 * The one form every "Enquire Now" entry point across the site posts
 * through (header CTA, Course Detail, Contact page) — POST
 * /api/v1/inquiries, the minimal capture-only slice built for this
 * milestone (see the `inquiries` migration's docblock for why there's
 * no admin inbox yet).
 */
export function EnquireNowModal({ open, onClose, courseId, courseName }: EnquireNowModalProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  function handleClose() {
    setForm(EMPTY_FORM);
    setErrors({});
    onClose();
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    const payload: InquiryPayload = {
      ...form,
      source_page: window.location.pathname,
      course_id: courseId ?? null,
    };

    try {
      await api.post<ApiResponse<InquiryConfirmation>>(ENDPOINTS.inquiries.submit, payload);
      showToast("Thanks — we'll be in touch shortly.", "success");
      handleClose();
    } catch (err) {
      const apiError = err as ApiError;
      setErrors(apiError.errors ?? {});
      showToast(apiError.message, "error");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Modal open={open} onClose={handleClose} title={courseName ? `Enquire about ${courseName}` : "Enquire Now"}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <Input
          label="Full name"
          required
          value={form.name}
          onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
          error={errors.name?.[0]}
        />
        <Input
          type="email"
          label="Email"
          required
          value={form.email}
          onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
          error={errors.email?.[0]}
        />
        <Input
          type="tel"
          label="Phone (optional)"
          value={form.phone}
          onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))}
          error={errors.phone?.[0]}
        />
        <Textarea
          label="Message"
          required
          rows={4}
          value={form.message}
          onChange={(e) => setForm((f) => ({ ...f, message: e.target.value }))}
          error={errors.message?.[0]}
        />
        <Button type="submit" isLoading={isSubmitting} className="mt-2">
          Submit Enquiry
        </Button>
      </form>
    </Modal>
  );
}
