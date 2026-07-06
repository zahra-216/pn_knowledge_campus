import { useParams } from "react-router-dom";
import { Calendar, User } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { NotFound } from "@/pages/public/NotFound";
import { Badge } from "@/components/ui";
import { RICH_TEXT_CLASSNAME } from "@/utils/richText";
import type { NewsArticle } from "@/types/news";

export function NewsDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: article, isLoading, error } = usePublicDetail<NewsArticle>(ENDPOINTS.news.public(slug));

  useSeoHead({
    title: article?.title ?? "News",
    description: article?.excerpt,
    canonicalPath: `/news/${slug}`,
    imageUrl: article?.featured_image_url,
    type: "article",
    seo: article?.seo,
    jsonLd: article
      ? {
          "@context": "https://schema.org",
          "@type": "NewsArticle",
          headline: article.title,
          datePublished: article.published_at,
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {article && (
        <article className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
          <Breadcrumb items={[{ label: "News", to: "/news" }, { label: article.title }]} />

          {article.category && <Badge tone="info" className="mt-4">{article.category.name}</Badge>}

          <h1 className="mt-3 font-display text-h1 font-semibold text-[color:var(--color-text)]">{article.title}</h1>

          <div className="mt-4 flex flex-wrap items-center gap-4 text-body-sm text-neutral-500">
            {article.author && (
              <span className="flex items-center gap-1.5">
                <User className="h-4 w-4" /> {article.author.name}
              </span>
            )}
            {article.published_at && (
              <span className="flex items-center gap-1.5">
                <Calendar className="h-4 w-4" />
                {new Date(article.published_at).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" })}
              </span>
            )}
          </div>

          {article.featured_image_url && (
            <img src={article.featured_image_url} alt={article.title} loading="lazy" decoding="async" className="mt-6 w-full rounded-lg object-cover" />
          )}

          <div className={`mt-8 ${RICH_TEXT_CLASSNAME}`} dangerouslySetInnerHTML={{ __html: article.body }} />

          {article.gallery.length > 0 && (
            <section className="mt-10">
              <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Gallery</h2>
              <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                {article.gallery.map((g) => (
                  <img key={g.id} src={g.thumb_url} alt="" loading="lazy" decoding="async" className="aspect-square w-full rounded-lg object-cover" />
                ))}
              </div>
            </section>
          )}
        </article>
      )}
    </AsyncState>
  );
}
