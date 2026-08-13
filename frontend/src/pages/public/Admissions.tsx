import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Accordion } from "@/components/public/Accordion";
import { Container } from "@/components/public/Container";
import { PageHero } from "@/components/public/PageHero";
import { Reveal } from "@/components/public/Reveal";
import { SectionHeading } from "@/components/public/SectionHeading";
import { SmartLink } from "@/components/public/SmartLink";
import { StatementBand } from "@/components/public/StatementBand";
import { NotFound } from "@/pages/public/NotFound";
import { findBlock } from "@/utils/pageBlocks";
import type { Page, HeroBlockData, CtaBlockData, FaqItem } from "@/types/page";

export function Admissions() {
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public("admissions"));
  const hero = findBlock<HeroBlockData>(page, "hero");
  const cta = findBlock<CtaBlockData>(page, "cta");
  const faq = findBlock<{ items: FaqItem[] }>(page, "faq");
  const media = useResolvedMedia([hero?.media_id]);
  const heroImage = hero?.media_id ? media.get(hero.media_id) : undefined;

  useSeoHead({
    title: page?.title ?? "Admissions",
    canonicalPath: "/admissions",
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
          <PageHero
            imageUrl={heroImage?.url}
            heading={hero?.heading ?? page.title}
            subheading={hero?.subheading}
            breadcrumbLabel="Admissions"
          />

          {cta?.heading && (
            <StatementBand tone="tint" containerSize="narrow">
              <div className="flex flex-col items-center gap-4 text-center">
                <h2 className="text-balance font-display text-h1 font-medium text-[color:var(--pub-ink)] dark:text-white">{cta.heading}</h2>
                {cta.body && <p className="max-w-xl text-body-lg text-[color:var(--pub-muted)]">{cta.body}</p>}
                {cta.button_url && cta.button_label && (
                  <SmartLink
                    to={cta.button_url}
                    className="mt-2 inline-flex h-12 items-center rounded-sm bg-gold px-7 text-body-sm font-semibold text-navy-dark transition-colors duration-200 hover:bg-gold-tint"
                  >
                    {cta.button_label}
                  </SmartLink>
                )}
              </div>
            </StatementBand>
          )}

          {faq?.items && faq.items.length > 0 && (
            <section className="py-[var(--space-lg)]">
              <Container size="narrow" className="flex flex-col gap-8">
                <Reveal>
                  <SectionHeading eyebrow="Questions" title="Admissions FAQ" align="left" />
                </Reveal>
                <Reveal delay={90}>
                  <Accordion items={faq.items.map((item, i) => ({ id: i, question: item.question, answer: item.answer }))} />
                </Reveal>
              </Container>
            </section>
          )}
        </div>
      )}
    </AsyncState>
  );
}
