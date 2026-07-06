import { useEffect, useState } from "react";
import { Modal, Button, Input, Textarea, Switch } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection } from "@/types/api";
import type { Download, DownloadCategory, DownloadPayload } from "@/types/download";

interface DownloadFormProps {
  open: boolean;
  download: Download | null;
  onClose: () => void;
  onSave: (payload: DownloadPayload) => Promise<void>;
}

const EMPTY: DownloadPayload = { title: "", description: "", category_id: null, order: 0, is_active: true };

export function DownloadForm({ open, download, onClose, onSave }: DownloadFormProps) {
  const [form, setForm] = useState<DownloadPayload>(EMPTY);
  const [categories, setCategories] = useState<DownloadCategory[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      download
        ? {
            title: download.title,
            description: download.description ?? "",
            category_id: download.category?.id ?? null,
            order: download.order,
            is_active: download.is_active,
          }
        : EMPTY
    );
  }, [download, open]);

  useEffect(() => {
    if (!open) return;
    api
      .get<ApiCollection<DownloadCategory>>(ENDPOINTS.downloadCategories.admin(), { params: { per_page: 100 } })
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
      title={download ? "Edit Download" : "New Download"}
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
        <Textarea
          label="Description (optional)"
          rows={3}
          value={form.description ?? ""}
          onChange={(e) => setForm({ ...form, description: e.target.value })}
        />

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

        <div className="flex flex-col gap-1.5">
          {download?.file_name && (
            <p className="text-body-sm text-neutral-500">
              Current file: <span className="font-medium text-[color:var(--color-text)]">{download.file_name}</span>
            </p>
          )}
          <MediaIdField label="File" type="document" mediaId={null} onChange={(id) => setForm({ ...form, media_id: id })} />
        </div>

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
