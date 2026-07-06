import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Modal, Input, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { NewsCategory } from "@/types/news";

/**
 * News Category Management — SRS Permission Matrix, "News" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access. Flat {name, slug, order} shape, same as Blog
 * Category — kept as a separate table/screen since News and Blog are
 * separate CMS modules with independent permissions.
 */
export function NewsCategories() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("news.create");
  const canDelete = can("news.delete");

  const [categories, setCategories] = useState<NewsCategory[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; category: NewsCategory | null }>({ open: false, category: null });
  const [name, setName] = useState("");
  const [order, setOrder] = useState(0);
  const [isSaving, setIsSaving] = useState(false);

  const fetchCategories = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<NewsCategory>>(ENDPOINTS.newsCategories.admin(), { params: { per_page: 100 } });
      setCategories(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("news.view")) return;
    fetchCategories();
  }, [fetchCategories, can]);

  function openForm(category: NewsCategory | null) {
    setFormState({ open: true, category });
    setName(category?.name ?? "");
    setOrder(category?.order ?? 0);
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      if (formState.category) {
        await api.put<ApiResponse<NewsCategory>>(ENDPOINTS.newsCategories.admin(formState.category.id), { name, order });
      } else {
        await api.post<ApiResponse<NewsCategory>>(ENDPOINTS.newsCategories.admin(), { name, order });
      }
      setFormState({ open: false, category: null });
      await fetchCategories();
    } catch {
      showToast("Could not save. The name may already be in use.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(category: NewsCategory) {
    await api.delete(ENDPOINTS.newsCategories.admin(category.id));
    await fetchCategories();
  }

  if (!can("news.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "News Categories" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to News Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<NewsCategory>[] = [
    { key: "name", header: "Name", render: (c) => c.name },
    { key: "slug", header: "Slug", render: (c) => <span className="text-neutral-500">{c.slug}</span> },
    { key: "news_count", header: "Articles", render: (c) => c.news_count ?? 0 },
    { key: "order", header: "Order", render: (c) => c.order },
    {
      key: "actions",
      header: "",
      render: (c) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(c)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(c)} aria-label={`Delete ${c.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "News Categories" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">News Categories</h1>
        {canCreate && (
          <Button onClick={() => openForm(null)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Category
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={categories}
          rowKey={(c) => c.id}
          isLoading={isLoading}
          emptyTitle="No categories yet"
          emptyDescription="Add categories like Press Releases, Announcements."
        />
      </Card>

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, category: null })}
        title={formState.category ? "Edit Category" : "New Category"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, category: null })}>
              Cancel
            </Button>
            <Button onClick={handleSave} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <Input label="Name" value={name} onChange={(e) => setName(e.target.value)} required />
          <Input label="Order" type="number" hint="Lower numbers appear first." value={order} onChange={(e) => setOrder(Number(e.target.value))} />
        </div>
      </Modal>
    </div>
  );
}
