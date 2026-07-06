import { useEffect, useState } from "react";
import { Modal, Button, Input, Switch } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection } from "@/types/api";
import type { Partner, PartnerCategory, PartnerPayload } from "@/types/homepage";

interface PartnerFormProps {
  open: boolean;
  partner: Partner | null;
  onClose: () => void;
  onSave: (payload: PartnerPayload) => Promise<void>;
}

const EMPTY: PartnerPayload = { name: "", url: "", category_id: null, order: 0, is_active: true };

export function PartnerForm({ open, partner, onClose, onSave }: PartnerFormProps) {
  const [form, setForm] = useState<PartnerPayload>(EMPTY);
  const [categories, setCategories] = useState<PartnerCategory[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      partner
        ? {
            name: partner.name,
            url: partner.url ?? "",
            category_id: partner.category?.id ?? null,
            order: partner.order,
            is_active: partner.is_active,
          }
        : EMPTY
    );
  }, [partner, open]);

  useEffect(() => {
    if (!open) return;
    api
      .get<ApiCollection<PartnerCategory>>(ENDPOINTS.partnerCategories.admin(), { params: { per_page: 100 } })
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
      title={partner ? "Edit Partner" : "New Partner"}
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
        <Input label="Website (optional)" value={form.url ?? ""} onChange={(e) => setForm({ ...form, url: e.target.value })} />

        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Partner Category (optional)</span>
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

        <MediaIdField label="Logo" type="image" mediaId={null} previewUrl={partner?.logo_url} onChange={(id) => setForm({ ...form, media_id: id })} />

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
