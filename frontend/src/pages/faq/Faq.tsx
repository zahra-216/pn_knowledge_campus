import { useCallback, useEffect, useState, type FormEvent } from "react";
import { Plus, Lock, Trash2, Search } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { FaqForm } from "./components/FaqForm";
import type { ApiCollection } from "@/types/api";
import type { Faq as FaqEntry, FaqCategory, FaqPayload } from "@/types/faq";

/**
 * Global Site FAQ Management (Milestone 17) — SRS Permission Matrix,
 * "FAQ" row: Super Admin/Administrator = Full; Content Editor/Marketing
 * = Create/Edit; Admissions = no access. Distinct from the Course
 * Detail "FAQ" tab (CourseFaqsTab), which manages the same `faqs` table's
 * course-scoped rows through a separate endpoint.
 */
export function Faq() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("faq.create");
  const canDelete = can("faq.delete");

  const [faqs, setFaqs] = useState<FaqEntry[]>([]);
  const [categories, setCategories] = useState<FaqCategory[]>([]);
  const [search, setSearch] = useState("");
  const [categoryFilter, setCategoryFilter] = useState("");
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; faq: FaqEntry | null }>({ open: false, faq: null });

  const fetchFaqs = useCallback(async (params: { search?: string; category?: string }) => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<FaqEntry>>(ENDPOINTS.faqs.admin(), {
        params: { per_page: 100, search: params.search || undefined, category: params.category || undefined },
      });
      setFaqs(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("faq.view")) return;
    fetchFaqs({ search, category: categoryFilter });
    api.get<ApiCollection<FaqCategory>>(ENDPOINTS.faqCategories.admin(), { params: { per_page: 100 } }).then(({ data }) => setCategories(data.data));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [can]);

  function handleSearchSubmit(e: FormEvent) {
    e.preventDefault();
    fetchFaqs({ search, category: categoryFilter });
  }

  function handleCategoryChange(slug: string) {
    setCategoryFilter(slug);
    fetchFaqs({ search, category: slug });
  }

  async function handleSave(payload: FaqPayload) {
    try {
      if (formState.faq) {
        await api.put(ENDPOINTS.faqs.admin(formState.faq.id), payload);
      } else {
        await api.post(ENDPOINTS.faqs.admin(), payload);
      }
      showToast("FAQ saved.", "success");
      await fetchFaqs({ search, category: categoryFilter });
    } catch {
      showToast("Could not save this FAQ.", "error");
    }
  }

  async function handleDelete(faq: FaqEntry) {
    await api.delete(ENDPOINTS.faqs.admin(faq.id));
    await fetchFaqs({ search, category: categoryFilter });
  }

  if (!can("faq.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "FAQ" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to FAQ.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<FaqEntry>[] = [
    { key: "question", header: "Question", render: (f) => f.question },
    { key: "category", header: "Category", render: (f) => f.category?.name ?? "—" },
    { key: "order", header: "Order", render: (f) => f.order },
    { key: "status", header: "Status", render: (f) => <Badge tone={f.is_active ? "success" : "neutral"}>{f.is_active ? "Active" : "Inactive"}</Badge> },
    {
      key: "actions",
      header: "",
      render: (f) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => setFormState({ open: true, faq: f })} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(f)} aria-label={`Delete ${f.question}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "FAQ" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">FAQ</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, faq: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New FAQ
          </Button>
        )}
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <form onSubmit={handleSearchSubmit} className="relative flex-1 min-w-[220px]">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search question or answer..."
            className="h-10 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
          />
        </form>

        <select
          value={categoryFilter}
          onChange={(e) => handleCategoryChange(e.target.value)}
          className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body-sm"
        >
          <option value="">All Categories</option>
          {categories.map((c) => (
            <option key={c.id} value={c.slug}>
              {c.name}
            </option>
          ))}
        </select>
      </div>

      <Card>
        <Table
          columns={columns}
          rows={faqs}
          rowKey={(f) => f.id}
          isLoading={isLoading}
          emptyTitle="No FAQs yet"
          emptyDescription="Add your first frequently asked question."
        />
      </Card>

      <FaqForm open={formState.open} faq={formState.faq} onClose={() => setFormState({ open: false, faq: null })} onSave={handleSave} />
    </div>
  );
}
