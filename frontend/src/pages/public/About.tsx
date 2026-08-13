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
import { cn } from "@/utils/cn";
import type { Page, HeroBlockData, TextBlockData } from "@/types/page";

/**
 * Hand-coded — reads its copy from the same Page Builder content an
 * admin already edits (slug "about"), but the layout itself is
 * purpose-built for this page rather than assembled from the generic
 * block renderer every other Static/Builder page still uses.
 */
export function About() {
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public("about"));
  const hero = findBlock<HeroBlockData>(page, "hero");
  const body = findBlock<TextBlockData>(page, "rich_text");
  const media = useResolvedMedia([hero?.media_id]);
  const heroImage = hero?.media_id ? media.get(hero.media_id) : undefined;

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
      {page && (
        <div>
          <PageHero
            imageUrl={heroImage?.url}
            heading={hero?.heading ?? page.title}
            subheading={hero?.subheading}
            breadcrumbLabel="About"
          />

          {body?.body && (
            <section className="py-[var(--space-lg)]">
              <Container size="narrow">
                <Reveal>
                  <div className={cn(RICH_TEXT_CLASSNAME, "text-body-lg")} dangerouslySetInnerHTML={{ __html: body.body }} />
                </Reveal>
              </Container>
            </section>
          )}
        </div>
      )}
    </AsyncState>
  );
}
