import { useEffect, useState } from "react";
import { Modal, Button, Input, useToast } from "@/components/ui";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { Page, PagePayload } from "@/types/page";

interface PageFormProps {
  open: boolean;
  page: Page | null;
  onClose: () => void;
  onSaved: (page: Page) => void | Promise<void>;
}

const EMPTY: PagePayload = { title: "", slug: "", template: "default" };

/**
 * Create/edit a Page's routing metadata (title/slug/template). Block
 * content is composed separately in the PageBuilder screen — this form
 * only covers what PageRequest validates.
 */
export function PageForm({ open, page, onClose, onSaved }: PageFormProps) {
  const [form, setForm] = useState<PagePayload>(EMPTY);
  const [isSaving, setIsSaving] = useState(false);
  const { showToast } = useToast();

  useEffect(() => {
    setForm(page ? { title: page.title, slug: page.slug, template: page.template } : EMPTY);
  }, [page, open]);

  async function handleSave() {
    setIsSaving(true);
    try {
      const response = page
        ? await api.put<ApiResponse<Page>>(ENDPOINTS.pages.admin(page.id), form)
        : await api.post<ApiResponse<Page>>(ENDPOINTS.pages.admin(), form);

      await onSaved(response.data.data);
      onClose();
    } catch {
      showToast("Could not save this page. Check the title and slug.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={page ? "Edit Page" : "New Page"}
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
        <Input label="Title" value={form.title ?? ""} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
        <Input
          label="Slug"
          hint="Auto-suggested from the title if left blank. Used as the public URL: /{slug}."
          value={form.slug ?? ""}
          onChange={(e) => setForm({ ...form, slug: e.target.value })}
        />
        <Input
          label="Template"
          hint="Optional layout variant flag (default: 'default')."
          value={form.template ?? ""}
          onChange={(e) => setForm({ ...form, template: e.target.value })}
        />
      </div>
    </Modal>
  );
}
