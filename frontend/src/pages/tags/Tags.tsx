import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Modal, Input, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { Tag } from "@/types/blog";

/**
 * Standalone Tag CRUD (API Design, Section 8.4) — separate from the
 * create-on-the-fly tagging in the Blog Post editor (TagInput), for
 * renaming/deleting tags directly. Gated by blog.* for now (see
 * BlogPolicy's docblock — shared infra with a future News module).
 */
export function Tags() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("blog.create");
  const canDelete = can("blog.delete");

  const [tags, setTags] = useState<Tag[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; tag: Tag | null }>({ open: false, tag: null });
  const [name, setName] = useState("");
  const [isSaving, setIsSaving] = useState(false);

  const fetchTags = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Tag>>(ENDPOINTS.tags.admin(), { params: { per_page: 100 } });
      setTags(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("blog.view")) return;
    fetchTags();
  }, [fetchTags, can]);

  function openForm(tag: Tag | null) {
    setFormState({ open: true, tag });
    setName(tag?.name ?? "");
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      if (formState.tag) {
        await api.put<ApiResponse<Tag>>(ENDPOINTS.tags.admin(formState.tag.id), { name });
      } else {
        await api.post<ApiResponse<Tag>>(ENDPOINTS.tags.admin(), { name });
      }
      setFormState({ open: false, tag: null });
      await fetchTags();
    } catch {
      showToast("Could not save. The name may already be in use.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(tag: Tag) {
    await api.delete(ENDPOINTS.tags.admin(tag.id));
    await fetchTags();
  }

  if (!can("blog.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Tags" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Blog Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Tag>[] = [
    { key: "name", header: "Name", render: (t) => t.name },
    { key: "slug", header: "Slug", render: (t) => <span className="text-neutral-500">{t.slug}</span> },
    { key: "posts_count", header: "Posts", render: (t) => t.posts_count ?? 0 },
    {
      key: "actions",
      header: "",
      render: (t) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(t)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(t)} aria-label={`Delete ${t.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Tags" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Tags</h1>
        {canCreate && (
          <Button onClick={() => openForm(null)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Tag
          </Button>
        )}
      </div>

      <Card>
        <Table columns={columns} rows={tags} rowKey={(t) => t.id} isLoading={isLoading} emptyTitle="No tags yet" emptyDescription="Tags are also created inline while writing a blog post." />
      </Card>

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, tag: null })}
        title={formState.tag ? "Edit Tag" : "New Tag"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, tag: null })}>
              Cancel
            </Button>
            <Button onClick={handleSave} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <Input label="Name" value={name} onChange={(e) => setName(e.target.value)} required />
      </Modal>
    </div>
  );
}
