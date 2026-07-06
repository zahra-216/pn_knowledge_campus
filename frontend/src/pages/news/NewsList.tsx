import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { NewsArticle, NewsStatus } from "@/types/news";

const STATUS_TONE: Record<NewsStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * News Management — SRS Permission Matrix, "News" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access. Same split as Blog.
 */
export function NewsList() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("news.create");
  const canDelete = can("news.delete");

  const [articles, setArticles] = useState<NewsArticle[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchArticles = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<NewsArticle>>(ENDPOINTS.news.admin(), { params: { per_page: 100 } });
      setArticles(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("news.view")) return;
    fetchArticles();
  }, [fetchArticles, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<NewsArticle>>(ENDPOINTS.news.admin(), {
        title: "New Article",
        body: "<p></p>",
      });
      navigate(`/admin/news/${data.data.id}`);
    } catch {
      showToast("Could not create a new article.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(article: NewsArticle) {
    await api.delete(ENDPOINTS.news.admin(article.id));
    await fetchArticles();
  }

  if (!can("news.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "News" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to News Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<NewsArticle>[] = [
    {
      key: "image",
      header: "",
      render: (n) =>
        n.featured_image_url ? (
          <img src={n.featured_image_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "title", header: "Title", render: (n) => n.title },
    { key: "category", header: "Category", render: (n) => n.category?.name ?? "—" },
    { key: "author", header: "Author", render: (n) => n.author?.name ?? "—" },
    {
      key: "status",
      header: "Status",
      render: (n) => (
        <div className="flex gap-1.5">
          {n.is_featured && <Badge tone="info">Featured</Badge>}
          <Badge tone={STATUS_TONE[n.status]}>{n.status}</Badge>
        </div>
      ),
    },
    { key: "views", header: "Views", render: (n) => n.views_count },
    {
      key: "actions",
      header: "",
      render: (n) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/news/${n.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(n)} aria-label={`Delete ${n.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "News" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">News</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Article
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={articles}
          rowKey={(n) => n.id}
          isLoading={isLoading}
          emptyTitle="No articles yet"
          emptyDescription="Write your first news article."
        />
      </Card>
    </div>
  );
}
