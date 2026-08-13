import { useParams } from "react-router-dom";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { PageBlockRenderer } from "@/components/public/blocks/PageBlockRenderer";
import { NotFound } from "@/pages/public/NotFound";
import type { Page } from "@/types/page";

/**
 * Renders any CMS Page Builder page by its slug — one component for
 * every "Static/Builder" route (About, Vision, Mission, Admissions,
 * How to Apply, Scholarships, Privacy Policy, ...) since they're all
 * interchangeable from the frontend's point of view: a slug plus an
 * ordered list of blocks. Matched last in the public route table so
 * every more specific route (courses, blog, ...) wins first.
 */
export function StaticPage() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public(slug));

  useSeoHead({
    // Audit fix (Medium remediation) — falling back to the raw URL slug
    // showed an unformatted, lowercase, hyphenated string ("how-to-apply")
    // as the browser tab title during any slow-network or transient-error
    // window before the real page data arrived.
    title: page?.title ?? "Loading…",
    canonicalPath: `/${slug}`,
    seo: page?.seo,
    jsonLd: page
      ? {
          "@context": "https://schema.org",
          "@type": "WebPage",
          name: page.title,
          description: page.seo?.meta_description ?? undefined,
        }
      : null,
  });

  if (error?.status === 404) {
    return <NotFound />;
  }

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {page && (
        <div>
          {page.blocks.length === 0 && (
            <div className="mx-auto max-w-3xl px-4 py-24 text-center">
              <h1 className="font-display text-h1 font-semibold text-[color:var(--color-text)]">{page.title}</h1>
            </div>
          )}
          <PageBlockRenderer blocks={page.blocks} />
        </div>
      )}
    </AsyncState>
  );
}
