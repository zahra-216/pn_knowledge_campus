import { useParams } from "react-router-dom";
import { Calendar, User } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { ContentCard } from "@/components/public/ContentCard";
import { NotFound } from "@/pages/public/NotFound";
import { Badge } from "@/components/ui";
import { RICH_TEXT_CLASSNAME } from "@/utils/richText";
import type { BlogPost } from "@/types/blog";

export function BlogDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: post, isLoading, error } = usePublicDetail<BlogPost>(ENDPOINTS.blog.public(slug));

  useSeoHead({
    title: post?.title ?? "Blog",
    description: post?.excerpt,
    canonicalPath: `/blog/${slug}`,
    imageUrl: post?.featured_image_url,
    type: "article",
    seo: post?.seo,
    jsonLd: post
      ? {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          headline: post.title,
          datePublished: post.published_at,
          author: post.author ? { "@type": "Person", name: post.author.name } : undefined,
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {post && (
        <article className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
          <Breadcrumb items={[{ label: "Blog", to: "/blog" }, { label: post.title }]} />

          <div className="mt-4 flex flex-wrap items-center gap-3">
            {post.category && <Badge tone="info">{post.category.name}</Badge>}
            {post.tags.map((t) => (
              <Badge key={t.id} tone="neutral">
                {t.name}
              </Badge>
            ))}
          </div>

          <h1 className="mt-3 font-display text-h1 font-semibold text-[color:var(--color-text)]">{post.title}</h1>

          <div className="mt-4 flex flex-wrap items-center gap-4 text-body-sm text-neutral-500">
            {post.author && (
              <span className="flex items-center gap-1.5">
                <User className="h-4 w-4" /> {post.author.name}
              </span>
            )}
            {post.published_at && (
              <span className="flex items-center gap-1.5">
                <Calendar className="h-4 w-4" />
                {new Date(post.published_at).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" })}
              </span>
            )}
          </div>

          {post.featured_image_url && (
            <img src={post.featured_image_url} alt={post.title} loading="lazy" decoding="async" className="mt-6 w-full rounded-lg object-cover" />
          )}

          <div className={`mt-8 ${RICH_TEXT_CLASSNAME}`} dangerouslySetInnerHTML={{ __html: post.body }} />

          {post.gallery.length > 0 && (
            <section className="mt-10">
              <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Gallery</h2>
              <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                {post.gallery.map((g) => (
                  <img key={g.id} src={g.thumb_url} alt="" loading="lazy" decoding="async" className="aspect-square w-full rounded-lg object-cover" />
                ))}
              </div>
            </section>
          )}

          {post.related_posts.length > 0 && (
            <section className="mt-12">
              <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Related Posts</h2>
              <div className="mt-4 grid gap-6 sm:grid-cols-2">
                {post.related_posts.map((r) => (
                  <ContentCard key={r.id} to={`/blog/${r.slug}`} image={r.featured_image_url} title={r.title} meta={r.category?.name} excerpt={r.excerpt} />
                ))}
              </div>
            </section>
          )}
        </article>
      )}
    </AsyncState>
  );
}
