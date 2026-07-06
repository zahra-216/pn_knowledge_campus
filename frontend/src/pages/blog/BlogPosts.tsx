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
import type { BlogPost, BlogPostStatus } from "@/types/blog";

const STATUS_TONE: Record<BlogPostStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * Blog Management — SRS Permission Matrix, "Blog" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access.
 */
export function BlogPosts() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("blog.create");
  const canDelete = can("blog.delete");

  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchPosts = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<BlogPost>>(ENDPOINTS.blog.admin(), { params: { per_page: 100 } });
      setPosts(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("blog.view")) return;
    fetchPosts();
  }, [fetchPosts, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<BlogPost>>(ENDPOINTS.blog.admin(), {
        title: "New Post",
        body: "<p></p>",
      });
      navigate(`/admin/blog/${data.data.id}`);
    } catch {
      showToast("Could not create a new post.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(post: BlogPost) {
    await api.delete(ENDPOINTS.blog.admin(post.id));
    await fetchPosts();
  }

  if (!can("blog.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Blog" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Blog Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<BlogPost>[] = [
    {
      key: "image",
      header: "",
      render: (p) =>
        p.featured_image_url ? (
          <img src={p.featured_image_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "title", header: "Title", render: (p) => p.title },
    { key: "category", header: "Category", render: (p) => p.category?.name ?? "—" },
    { key: "author", header: "Author", render: (p) => p.author?.name ?? "—" },
    {
      key: "status",
      header: "Status",
      render: (p) => (
        <div className="flex gap-1.5">
          {p.is_featured && <Badge tone="info">Featured</Badge>}
          <Badge tone={STATUS_TONE[p.status]}>{p.status}</Badge>
        </div>
      ),
    },
    { key: "views", header: "Views", render: (p) => p.views_count },
    {
      key: "actions",
      header: "",
      render: (p) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/blog/${p.id}`)} className="text-body-sm text-navy hover:underline">
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
      <Breadcrumb items={[{ label: "Blog" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Blog</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Post
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={posts}
          rowKey={(p) => p.id}
          isLoading={isLoading}
          emptyTitle="No posts yet"
          emptyDescription="Write your first blog post."
        />
      </Card>
    </div>
  );
}
