import { useState, type FormEvent } from "react";
import { Modal } from "@/components/ui/Modal";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiError, ApiResponse } from "@/types/api";
import type { DownloadRequestPayload } from "@/types/download";

interface RequestDownloadModalProps {
  open: boolean;
  onClose: () => void;
  downloadId: number;
  downloadTitle: string;
}

const EMPTY_FORM = { name: "", email: "", phone: "" };

/**
 * Audit fix (High remediation) — the capture form a visitor fills in to
 * unlock a gated download (Download.requires_inquiry). Posts to
 * POST /downloads/{id}/request, which both logs a real Inquiry (staff
 * get notified the same way a Contact-form submission would) and
 * returns a signed, time-limited URL — see
 * DownloadController::requestDownload()'s docblock.
 */
export function RequestDownloadModal({ open, onClose, downloadId, downloadTitle }: RequestDownloadModalProps) {
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

    try {
      const response = await api.post<ApiResponse<{ url: string }>>(
        ENDPOINTS.downloads.request(downloadId),
        form satisfies DownloadRequestPayload,
      );
      window.open(response.data.data.url, "_blank", "noopener,noreferrer");
      showToast("Thanks — your download is starting.", "success");
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
    <Modal open={open} onClose={handleClose} title={`Download ${downloadTitle}`}>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <p className="text-body-sm text-neutral-500">Enter your details to receive the download link.</p>
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
        <Button type="submit" isLoading={isSubmitting} className="mt-2">
          Get Download Link
        </Button>
      </form>
    </Modal>
  );
}
