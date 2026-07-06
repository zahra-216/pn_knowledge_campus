import { useCallback, useEffect, useState } from "react";
import { Plus, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Button, Table, Badge, Modal, Input, Switch, useToast, type TableColumn } from "@/components/ui";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { SocialLink, SocialLinkPayload } from "@/types/socialLink";

const EMPTY_FORM: SocialLinkPayload = { platform: "", url: "", is_active: true };

/** UI/UX Design, Admin Sitemap — "Contact & Social" tab. */
export function SocialLinksPanel() {
  const { showToast } = useToast();
  const [links, setLinks] = useState<SocialLink[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState<SocialLinkPayload>(EMPTY_FORM);
  const [editingId, setEditingId] = useState<number | null>(null);

  const fetchLinks = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<SocialLink>>(ENDPOINTS.socialLinks.admin());
      setLinks(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchLinks();
  }, [fetchLinks]);

  function openCreate() {
    setForm(EMPTY_FORM);
    setEditingId(null);
    setIsModalOpen(true);
  }

  function openEdit(link: SocialLink) {
    setForm(link);
    setEditingId(link.id);
    setIsModalOpen(true);
  }

  async function handleSave() {
    try {
      if (editingId) {
        await api.put<ApiResponse<SocialLink>>(ENDPOINTS.socialLinks.admin(editingId), form);
      } else {
        await api.post<ApiResponse<SocialLink>>(ENDPOINTS.socialLinks.admin(), form);
      }
      setIsModalOpen(false);
      await fetchLinks();
      showToast("Social link saved.", "success");
    } catch {
      showToast("Could not save this link.", "error");
    }
  }

  async function handleDelete(id: number) {
    await api.delete(ENDPOINTS.socialLinks.admin(id));
    await fetchLinks();
  }

  const columns: TableColumn<SocialLink>[] = [
    { key: "platform", header: "Platform", render: (l) => l.platform },
    { key: "url", header: "URL", render: (l) => <span className="truncate">{l.url}</span> },
    {
      key: "status",
      header: "Status",
      render: (l) => <Badge tone={l.is_active ? "success" : "neutral"}>{l.is_active ? "Active" : "Inactive"}</Badge>,
    },
    {
      key: "actions",
      header: "",
      render: (l) => (
        <div className="flex gap-2">
          <button type="button" onClick={() => openEdit(l)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          <button type="button" onClick={() => handleDelete(l.id)} aria-label={`Delete ${l.platform}`}>
            <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-3">
      <div className="flex items-center justify-between">
        <h3 className="text-h4 font-display font-semibold text-[color:var(--color-text)]">Social Links</h3>
        <Button size="sm" onClick={openCreate}>
          <Plus className="h-4 w-4" aria-hidden="true" />
          New Link
        </Button>
      </div>

      <Table
        columns={columns}
        rows={links}
        rowKey={(l) => l.id}
        isLoading={isLoading}
        emptyTitle="No social links yet"
        emptyDescription="Add links for Facebook, Instagram, LinkedIn, etc."
      />

      <Modal
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingId ? "Edit Social Link" : "New Social Link"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setIsModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSave}>Save</Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input
            label="Platform"
            placeholder="facebook, instagram, linkedin..."
            value={form.platform ?? ""}
            onChange={(e) => setForm({ ...form, platform: e.target.value })}
            required
          />
          <Input label="URL" type="url" value={form.url ?? ""} onChange={(e) => setForm({ ...form, url: e.target.value })} required />
          <Switch label="Active" checked={form.is_active ?? true} onChange={(checked) => setForm({ ...form, is_active: checked })} />
        </div>
      </Modal>
    </div>
  );
}
