import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Modal, Input, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { CourseLookup } from "@/types/course";

interface CourseLookupPageProps {
  title: string;
  emptyDescription: string;
  endpoint: (id?: number) => string;
}

/**
 * Shared admin screen for CourseLevel/CourseMode/CourseCategory — all
 * three are the same {name, slug, order} shape (see the backend's
 * CourseLookupController), so one component covers all three rather
 * than three near-duplicate list+form pages. Gated by courses.* — these
 * are Course Management sub-parts, not modules of their own.
 */
export function CourseLookupPage({ title, emptyDescription, endpoint }: CourseLookupPageProps) {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("courses.create");
  const canDelete = can("courses.delete");

  const [items, setItems] = useState<CourseLookup[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; item: CourseLookup | null }>({ open: false, item: null });
  const [name, setName] = useState("");
  const [order, setOrder] = useState(0);
  const [isSaving, setIsSaving] = useState(false);

  const fetchItems = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<CourseLookup>>(endpoint(), { params: { per_page: 100 } });
      setItems(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [endpoint]);

  useEffect(() => {
    if (!can("courses.view")) return;
    fetchItems();
  }, [fetchItems, can]);

  function openForm(item: CourseLookup | null) {
    setFormState({ open: true, item });
    setName(item?.name ?? "");
    setOrder(item?.order ?? 0);
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      if (formState.item) {
        await api.put<ApiResponse<CourseLookup>>(endpoint(formState.item.id), { name, order });
      } else {
        await api.post<ApiResponse<CourseLookup>>(endpoint(), { name, order });
      }
      setFormState({ open: false, item: null });
      await fetchItems();
    } catch {
      showToast("Could not save. The name may already be in use.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(item: CourseLookup) {
    await api.delete(endpoint(item.id));
    await fetchItems();
  }

  if (!can("courses.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: title }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Course Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<CourseLookup>[] = [
    { key: "name", header: "Name", render: (i) => i.name },
    { key: "slug", header: "Slug", render: (i) => <span className="text-neutral-500">{i.slug}</span> },
    { key: "order", header: "Order", render: (i) => i.order },
    {
      key: "actions",
      header: "",
      render: (i) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(i)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(i)} aria-label={`Delete ${i.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: title }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{title}</h1>
        {canCreate && (
          <Button onClick={() => openForm(null)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New {title.replace(/s$/, "")}
          </Button>
        )}
      </div>

      <Card>
        <Table columns={columns} rows={items} rowKey={(i) => i.id} isLoading={isLoading} emptyTitle={`No ${title.toLowerCase()} yet`} emptyDescription={emptyDescription} />
      </Card>

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, item: null })}
        title={formState.item ? `Edit ${title.replace(/s$/, "")}` : `New ${title.replace(/s$/, "")}`}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, item: null })}>
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
