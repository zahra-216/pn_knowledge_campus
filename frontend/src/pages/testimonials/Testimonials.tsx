import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { TestimonialForm } from "./components/TestimonialForm";
import type { ApiCollection } from "@/types/api";
import type { Testimonial, TestimonialPayload } from "@/types/homepage";

/**
 * SRS Permission Matrix, "Testimonials" row — Super Admin/Administrator =
 * Full; Content Editor/Marketing = Create/Edit; Admissions = no access.
 */
export function Testimonials() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("testimonials.create");
  const canDelete = can("testimonials.delete");

  const [testimonials, setTestimonials] = useState<Testimonial[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; testimonial: Testimonial | null }>({ open: false, testimonial: null });

  const fetchTestimonials = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Testimonial>>(ENDPOINTS.testimonials.admin(), { params: { per_page: 100 } });
      setTestimonials(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("testimonials.view")) return;
    fetchTestimonials();
  }, [fetchTestimonials, can]);

  async function handleSave(payload: TestimonialPayload) {
    try {
      if (formState.testimonial) {
        await api.put(ENDPOINTS.testimonials.admin(formState.testimonial.id), payload);
      } else {
        await api.post(ENDPOINTS.testimonials.admin(), payload);
      }
      showToast("Testimonial saved.", "success");
      await fetchTestimonials();
    } catch {
      showToast("Could not save this testimonial.", "error");
    }
  }

  async function handleDelete(testimonial: Testimonial) {
    await api.delete(ENDPOINTS.testimonials.admin(testimonial.id));
    await fetchTestimonials();
  }

  if (!can("testimonials.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Testimonials" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Testimonials.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Testimonial>[] = [
    { key: "name", header: "Name", render: (t) => t.name },
    { key: "role_title", header: "Role", render: (t) => t.role_title ?? "—" },
    {
      key: "status",
      header: "Status",
      render: (t) => (
        <div className="flex gap-1.5">
          {t.is_featured && <Badge tone="info">Featured</Badge>}
          <Badge tone={t.is_active ? "success" : "neutral"}>{t.is_active ? "Active" : "Inactive"}</Badge>
        </div>
      ),
    },
    {
      key: "actions",
      header: "",
      render: (t) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => setFormState({ open: true, testimonial: t })} className="text-body-sm text-navy hover:underline">
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
      <Breadcrumb items={[{ label: "Testimonials" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Testimonials</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, testimonial: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Testimonial
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={testimonials}
          rowKey={(t) => t.id}
          isLoading={isLoading}
          emptyTitle="No testimonials yet"
          emptyDescription="Add your first student or alumni testimonial."
        />
      </Card>

      <TestimonialForm
        open={formState.open}
        testimonial={formState.testimonial}
        onClose={() => setFormState({ open: false, testimonial: null })}
        onSave={handleSave}
      />
    </div>
  );
}
