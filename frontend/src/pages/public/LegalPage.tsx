import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Container } from "@/components/public/Container";
import { PageHero } from "@/components/public/PageHero";
import { Reveal } from "@/components/public/Reveal";
import { NotFound } from "@/pages/public/NotFound";
import { findBlock } from "@/utils/pageBlocks";
import { RICH_TEXT_CLASSNAME } from "@/utils/richText";
import type { Page, HeroBlockData, TextBlockData } from "@/types/page";

/**
 * One shared, hand-coded template for the three legal pages (Privacy
 * Policy, Terms, Refund Policy) — deliberately narrow in scope to this
 * one content shape (hero + a single prose block + a last-updated
 * date), not a reintroduction of the generic multi-block-type renderer
 * these pages are moving away from.
 */
export function LegalPage({ slug, path, breadcrumbLabel }: { slug: string; path: string; breadcrumbLabel: string }) {
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public(slug));
  const hero = findBlock<HeroBlockData>(page, "hero");
  const body = findBlock<TextBlockData>(page, "rich_text");
  const media = useResolvedMedia([hero?.media_id]);
  const heroImage = hero?.media_id ? media.get(hero.media_id) : undefined;
  const lastUpdated = page
    ? new Date(page.updated_at).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" })
    : null;

  useSeoHead({
    title: page?.title ?? breadcrumbLabel,
    canonicalPath: path,
    seo: page?.seo,
    jsonLd: page
      ? { "@context": "https://schema.org", "@type": "WebPage", name: page.title, description: page.seo?.meta_description ?? undefined }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {page && (
        <div>
          <PageHero imageUrl={heroImage?.url} heading={hero?.heading ?? page.title} subheading={hero?.subheading} breadcrumbLabel={breadcrumbLabel} />

          <section className="py-[var(--space-lg)]">
            <Container size="narrow" className="flex flex-col gap-6">
              {lastUpdated && <p className="text-body-sm text-[color:var(--pub-muted)]">Last updated: {lastUpdated}</p>}
              {body?.body && (
                <Reveal>
                  <div className={RICH_TEXT_CLASSNAME} dangerouslySetInnerHTML={{ __html: body.body }} />
                </Reveal>
              )}
            </Container>
          </section>
        </div>
      )}
    </AsyncState>
  );
}
