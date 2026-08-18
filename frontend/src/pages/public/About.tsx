import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { PageBlockRenderer } from "@/components/public/blocks/PageBlockRenderer";
import { NotFound } from "@/pages/public/NotFound";
import type { Page } from "@/types/page";

/**
 * Renders the About page's full block list (hero, intro, Vision,
 * Mission, Chairman's Message, Manager's Message) via the shared
 * PageBlockRenderer — previously hand-rolled to only render hero +
 * a single rich_text block, which silently dropped every other block
 * type once Vision/Mission/Chairman's/Manager's Message were merged
 * into this page's block list. Now matches StaticPage.tsx's pattern.
 */
export function About() {
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public("about"));

  useSeoHead({
    title: page?.title ?? "About",
    canonicalPath: "/about",
    seo: page?.seo,
    jsonLd: page
      ? { "@context": "https://schema.org", "@type": "AboutPage", name: page.title, description: page.seo?.meta_description ?? undefined }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {page && <PageBlockRenderer blocks={page.blocks} />}
    </AsyncState>
  );
}