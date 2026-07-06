import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock, UploadCloud } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, Button, Badge, type TabItem } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { BlogPostContentTab } from "./components/BlogPostContentTab";
import { BlogPostMediaTab } from "./components/BlogPostMediaTab";
import type { ApiResponse } from "@/types/api";
import type { BlogPost, BlogPostPayload, BlogPostStatus } from "@/types/blog";

const TABS: TabItem[] = [
  { key: "content", label: "Content" },
  { key: "media", label: "Media" },
  { key: "seo", label: "SEO" },
];

const STATUS_TONE: Record<BlogPostStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

export function BlogPostEditor() {
  const { id } = useParams<{ id: string }>();
  const postId = Number(id);
  const { can } = usePermission();
  const canEdit = can("blog.edit");
  const canPublish = can("blog.publish");

  const [activeTab, setActiveTab] = useState("content");
  const [post, setPost] = useState<BlogPost | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchPost = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<BlogPost>>(ENDPOINTS.blog.admin(postId));
      setPost(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [postId]);

  useEffect(() => {
    if (!can("blog.view")) return;
    fetchPost();
  }, [fetchPost, can]);

  async function handleSave(payload: BlogPostPayload) {
    const { data } = await api.put<ApiResponse<BlogPost>>(ENDPOINTS.blog.admin(postId), payload);
    setPost(data.data);
  }

  async function handlePublish() {
    await api.patch(ENDPOINTS.blog.publish(postId));
    await fetchPost();
  }

  if (!can("blog.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Blog", to: "/admin/blog" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Blog Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !post) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Blog", to: "/admin/blog" }, { label: post.title }]} />

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{post.title}</h1>
          <Badge tone={STATUS_TONE[post.status]}>{post.status}</Badge>
        </div>
        {canPublish && post.status !== "published" && (
          <Button variant="secondary" onClick={handlePublish}>
            <UploadCloud className="h-4 w-4" aria-hidden="true" />
            Publish
          </Button>
        )}
      </div>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "content" && <BlogPostContentTab post={post} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "media" && <BlogPostMediaTab post={post} canEdit={canEdit} onSave={handleSave} onRefresh={fetchPost} />}
          {activeTab === "seo" && <SeoFieldsPanel type="blog" id={post.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
