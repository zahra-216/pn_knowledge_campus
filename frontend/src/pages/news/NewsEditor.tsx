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
import { NewsContentTab } from "./components/NewsContentTab";
import { NewsMediaTab } from "./components/NewsMediaTab";
import type { ApiResponse } from "@/types/api";
import type { NewsArticle, NewsArticlePayload, NewsStatus } from "@/types/news";

const TABS: TabItem[] = [
  { key: "content", label: "Content" },
  { key: "media", label: "Media" },
  { key: "seo", label: "SEO" },
];

const STATUS_TONE: Record<NewsStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

export function NewsEditor() {
  const { id } = useParams<{ id: string }>();
  const articleId = Number(id);
  const { can } = usePermission();
  const canEdit = can("news.edit");
  const canPublish = can("news.publish");

  const [activeTab, setActiveTab] = useState("content");
  const [article, setArticle] = useState<NewsArticle | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchArticle = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<NewsArticle>>(ENDPOINTS.news.admin(articleId));
      setArticle(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [articleId]);

  useEffect(() => {
    if (!can("news.view")) return;
    fetchArticle();
  }, [fetchArticle, can]);

  async function handleSave(payload: NewsArticlePayload) {
    const { data } = await api.put<ApiResponse<NewsArticle>>(ENDPOINTS.news.admin(articleId), payload);
    setArticle(data.data);
  }

  async function handlePublish() {
    await api.patch(ENDPOINTS.news.publish(articleId));
    await fetchArticle();
  }

  if (!can("news.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "News", to: "/admin/news" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to News Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !article) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "News", to: "/admin/news" }, { label: article.title }]} />

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{article.title}</h1>
          <Badge tone={STATUS_TONE[article.status]}>{article.status}</Badge>
        </div>
        {canPublish && article.status !== "published" && (
          <Button variant="secondary" onClick={handlePublish}>
            <UploadCloud className="h-4 w-4" aria-hidden="true" />
            Publish
          </Button>
        )}
      </div>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "content" && <NewsContentTab article={article} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "media" && <NewsMediaTab article={article} canEdit={canEdit} onSave={handleSave} onRefresh={fetchArticle} />}
          {activeTab === "seo" && <SeoFieldsPanel type="news" id={article.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
