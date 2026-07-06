import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { HeroSlideForm } from "./components/HeroSlideForm";
import type { ApiCollection } from "@/types/api";
import type { HeroSlide, HeroSlidePayload } from "@/types/homepage";

/**
 * SRS Permission Matrix, "Hero Slider" row — Super Admin/Administrator =
 * Full; Marketing = Create/Edit; Content Editor/Admissions = no access.
 */
export function HeroSlides() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("hero_slides.create");
  const canDelete = can("hero_slides.delete");

  const [slides, setSlides] = useState<HeroSlide[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; slide: HeroSlide | null }>({ open: false, slide: null });

  const fetchSlides = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<HeroSlide>>(ENDPOINTS.heroSlides.admin(), { params: { per_page: 100 } });
      setSlides(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("hero_slides.view")) return;
    fetchSlides();
  }, [fetchSlides, can]);

  async function handleSave(payload: HeroSlidePayload) {
    try {
      if (formState.slide) {
        await api.put(ENDPOINTS.heroSlides.admin(formState.slide.id), payload);
      } else {
        await api.post(ENDPOINTS.heroSlides.admin(), payload);
      }
      showToast("Hero slide saved.", "success");
      await fetchSlides();
    } catch {
      showToast("Could not save this slide.", "error");
    }
  }

  async function handleDelete(slide: HeroSlide) {
    await api.delete(ENDPOINTS.heroSlides.admin(slide.id));
    await fetchSlides();
  }

  if (!can("hero_slides.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Hero Slider" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to the Hero Slider.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<HeroSlide>[] = [
    {
      key: "image",
      header: "",
      render: (s) =>
        s.thumb_url ? (
          <img src={s.thumb_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "title", header: "Title", render: (s) => s.title },
    { key: "order", header: "Order", render: (s) => s.order },
    { key: "status", header: "Status", render: (s) => <Badge tone={s.is_active ? "success" : "neutral"}>{s.is_active ? "Active" : "Inactive"}</Badge> },
    {
      key: "actions",
      header: "",
      render: (s) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => setFormState({ open: true, slide: s })} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(s)} aria-label={`Delete ${s.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Hero Slider" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Hero Slider</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, slide: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Slide
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={slides}
          rowKey={(s) => s.id}
          isLoading={isLoading}
          emptyTitle="No hero slides yet"
          emptyDescription="Add your first homepage banner slide."
        />
      </Card>

      <HeroSlideForm
        open={formState.open}
        slide={formState.slide}
        onClose={() => setFormState({ open: false, slide: null })}
        onSave={handleSave}
      />
    </div>
  );
}
