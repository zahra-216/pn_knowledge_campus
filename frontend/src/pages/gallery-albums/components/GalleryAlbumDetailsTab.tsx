import { useEffect, useState } from "react";
import { Button, Input, Textarea, Switch, useToast } from "@/components/ui";
import type { GalleryAlbum, GalleryAlbumPayload } from "@/types/gallery";

interface GalleryAlbumDetailsTabProps {
  album: GalleryAlbum;
  canEdit: boolean;
  onSave: (payload: GalleryAlbumPayload) => Promise<void>;
}

export function GalleryAlbumDetailsTab({ album, canEdit, onSave }: GalleryAlbumDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<GalleryAlbumPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      title: album.title,
      slug: album.slug,
      description: album.description ?? "",
      order: album.order,
      is_active: album.is_active,
    });
  }, [album]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Album saved.", "success");
    } catch {
      showToast("Could not save. Check the title/slug.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Title" value={form.title ?? ""} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the title if left blank. Public URL: /gallery-albums/{slug}."
        value={form.slug ?? ""}
        onChange={(e) => setForm({ ...form, slug: e.target.value })}
      />
      <Textarea
        label="Description"
        value={form.description ?? ""}
        onChange={(e) => setForm({ ...form, description: e.target.value })}
        rows={4}
      />

      <div className="grid grid-cols-2 gap-3">
        <Input
          label="Order"
          type="number"
          hint="Lower numbers appear first."
          value={form.order ?? 0}
          onChange={(e) => setForm({ ...form, order: Number(e.target.value) })}
        />
        <div className="flex items-end pb-2">
          <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
        </div>
      </div>

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Album
        </Button>
      )}
    </fieldset>
  );
}
