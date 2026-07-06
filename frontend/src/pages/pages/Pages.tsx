import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection } from "@/types/api";
import type { Page, PageStatus } from "@/types/page";
import { PageForm } from "./components/PageForm";

const STATUS_TONE: Record<PageStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * Page Builder — list of static/informational pages (Database Design,
 * Section 4.5; SRS Section 6.5's "Static/Builder Pages"). UI/UX Design,
 * Admin Sitemap: Content > Pages.
 */
export function Pages() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const canCreate = can("pages.create");
  const canDelete = can("pages.delete");

  const [pages, setPages] = useState<Page[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isFormOpen, setIsFormOpen] = useState(false);

  const fetchPages = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Page>>(ENDPOINTS.pages.admin(), { params: { per_page: 100 } });
      setPages(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("pages.view")) return;
    fetchPages();
  }, [fetchPages, can]);

  async function handleDelete(page: Page) {
    await api.delete(ENDPOINTS.pages.admin(page.id));
    await fetchPages();
  }

  if (!can("pages.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Pages" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to the Page Builder.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Page>[] = [
    { key: "title", header: "Title", render: (p) => p.title },
    { key: "slug", header: "Slug", render: (p) => <span className="text-neutral-500">/{p.slug}</span> },
    { key: "status", header: "Status", render: (p) => <Badge tone={STATUS_TONE[p.status]}>{p.status}</Badge> },
    { key: "blocks", header: "Blocks", render: (p) => p.blocks?.length ?? 0 },
    {
      key: "actions",
      header: "",
      render: (p) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/pages/${p.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(p)} aria-label={`Delete ${p.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Pages" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Pages</h1>
        {canCreate && (
          <Button onClick={() => setIsFormOpen(true)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Page
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={pages}
          rowKey={(p) => p.id}
          isLoading={isLoading}
          emptyTitle="No pages yet"
          emptyDescription="Create your first static page (e.g. About, Vision, Mission)."
        />
      </Card>

      <PageForm
        open={isFormOpen}
        page={null}
        onClose={() => setIsFormOpen(false)}
        onSaved={async (created) => {
          setIsFormOpen(false);
          await fetchPages();
          navigate(`/admin/pages/${created.id}`);
        }}
      />
    </div>
  );
}
