import { useEffect, useState } from "react";
import { Modal, Button, Input, Switch } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import type { HeroSlide, HeroSlidePayload } from "@/types/homepage";

interface HeroSlideFormProps {
  open: boolean;
  slide: HeroSlide | null;
  onClose: () => void;
  onSave: (payload: HeroSlidePayload) => Promise<void>;
}

const EMPTY: HeroSlidePayload = { title: "", subtitle: "", cta_text: "", cta_url: "", is_active: true };

export function HeroSlideForm({ open, slide, onClose, onSave }: HeroSlideFormProps) {
  const [form, setForm] = useState<HeroSlidePayload>(EMPTY);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      slide
        ? {
            title: slide.title,
            subtitle: slide.subtitle ?? "",
            cta_text: slide.cta_text ?? "",
            cta_url: slide.cta_url ?? "",
            starts_at: slide.starts_at ?? "",
            ends_at: slide.ends_at ?? "",
            is_active: slide.is_active,
            order: slide.order,
          }
        : EMPTY
    );
  }, [slide, open]);

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
      title={slide ? "Edit Hero Slide" : "New Hero Slide"}
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
        <Input label="Subtitle" value={form.subtitle ?? ""} onChange={(e) => setForm({ ...form, subtitle: e.target.value })} />

        <MediaIdField
          label="Background Image"
          type="image"
          mediaId={null}
          previewUrl={slide?.thumb_url ?? slide?.image_url}
          onChange={(id) => setForm({ ...form, media_id: id })}
        />

        <div className="grid grid-cols-2 gap-3">
          <Input label="CTA Label" value={form.cta_text ?? ""} onChange={(e) => setForm({ ...form, cta_text: e.target.value })} />
          <Input label="CTA URL" value={form.cta_url ?? ""} onChange={(e) => setForm({ ...form, cta_url: e.target.value })} />
        </div>

        <div className="grid grid-cols-2 gap-3">
          <Input
            label="Starts"
            type="datetime-local"
            value={form.starts_at ?? ""}
            onChange={(e) => setForm({ ...form, starts_at: e.target.value })}
          />
          <Input
            label="Ends"
            type="datetime-local"
            value={form.ends_at ?? ""}
            onChange={(e) => setForm({ ...form, ends_at: e.target.value })}
          />
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
