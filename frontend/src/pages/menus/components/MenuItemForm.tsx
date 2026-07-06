import { useEffect, useState } from "react";
import { Modal, Button, Input, Switch } from "@/components/ui";
import type { MenuItem, MenuItemPayload } from "@/types/menu";

interface MenuItemFormProps {
  open: boolean;
  item: MenuItem | null;
  onClose: () => void;
  onSave: (payload: MenuItemPayload) => Promise<void>;
}

const EMPTY: MenuItemPayload = {
  label: "",
  custom_url: "",
  description: "",
  icon: "",
  is_mega_menu: false,
  open_in_new_tab: false,
  is_active: true,
  visible_on: "both",
};

/**
 * UI/UX Design, Section 6.4 (Tabs/Modal patterns). The "internal link"
 * option is present but disabled with an explanatory note — no content
 * model (Page, Course, ...) is registered in config('menus.linkable_types')
 * yet, so every item today must be a custom URL (backend-enforced, this
 * is just making that constraint visible rather than surprising).
 */
export function MenuItemForm({ open, item, onClose, onSave }: MenuItemFormProps) {
  const [form, setForm] = useState<MenuItemPayload>(EMPTY);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm(
      item
        ? {
            label: item.label,
            custom_url: item.custom_url ?? "",
            description: item.description ?? "",
            icon: item.icon ?? "",
            is_mega_menu: item.is_mega_menu,
            open_in_new_tab: item.open_in_new_tab,
            is_active: item.is_active,
            starts_at: item.starts_at ?? "",
            ends_at: item.ends_at ?? "",
            visible_on: item.visible_on,
          }
        : EMPTY
    );
  }, [item, open]);

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
      title={item ? "Edit Menu Item" : "New Menu Item"}
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
        <Input label="Label" value={form.label ?? ""} onChange={(e) => setForm({ ...form, label: e.target.value })} required />

        <div className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Link</span>
          <Input
            placeholder="/path or https://example.com, or # for a dropdown-only parent"
            value={form.custom_url ?? ""}
            onChange={(e) => setForm({ ...form, custom_url: e.target.value })}
          />
          <p className="text-caption text-neutral-500">
            Linking to internal content (Courses, Pages, ...) isn't available yet — no content module is registered. Use a custom
            URL for now.
          </p>
        </div>

        <Input
          label="Description"
          hint="Optional subtitle shown in mega-menu dropdowns."
          value={form.description ?? ""}
          onChange={(e) => setForm({ ...form, description: e.target.value })}
        />

        <Input
          label="Icon"
          hint="A Lucide icon name (e.g. graduation-cap), optional."
          value={form.icon ?? ""}
          onChange={(e) => setForm({ ...form, icon: e.target.value })}
        />

        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Visible on</span>
          <select
            value={form.visible_on}
            onChange={(e) => setForm({ ...form, visible_on: e.target.value as MenuItemPayload["visible_on"] })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="both">Both desktop and mobile</option>
            <option value="desktop">Desktop only</option>
            <option value="mobile">Mobile only</option>
          </select>
        </label>

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

        <Switch
          label="Mega menu (render this dropdown as a multi-column layout)"
          checked={form.is_mega_menu ?? false}
          onChange={(checked) => setForm({ ...form, is_mega_menu: checked })}
        />
        <Switch
          label="Open in new tab"
          checked={form.open_in_new_tab ?? false}
          onChange={(checked) => setForm({ ...form, open_in_new_tab: checked })}
        />
        <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
      </div>
    </Modal>
  );
}
