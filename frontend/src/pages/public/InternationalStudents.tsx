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
import { NotFound } from "@/pages/public/NotFound";
import { findBlock } from "@/utils/pageBlocks";
import type { Page, HeroBlockData, TextBlockData, FaqItem, GalleryBlockData } from "@/types/page";

export function InternationalStudents() {
  const { data: page, isLoading, error } = usePublicDetail<Page>(ENDPOINTS.pages.public("international-students"));
  const hero = findBlock<HeroBlockData>(page, "hero");
  const body = findBlock<TextBlockData>(page, "text");
  const faq = findBlock<{ items: FaqItem[] }>(page, "faq");
  const gallery = findBlock<GalleryBlockData>(page, "gallery");
  const media = useResolvedMedia([hero?.media_id, ...(gallery?.media_ids ?? [])]);
  const heroImage = hero?.media_id ? media.get(hero.media_id) : undefined;

  useSeoHead({
    title: page?.title ?? "International Students",
    canonicalPath: "/international-students",
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
            breadcrumbLabel="International Students"
          />

          {body?.body && (
            <section className="py-[var(--space-lg)]">
              <Container size="narrow">
                <Reveal>
                  <p className="whitespace-pre-line text-body-lg text-[color:var(--pub-muted)]">{body.body}</p>
                </Reveal>
              </Container>
            </section>
          )}

          {gallery && gallery.media_ids.length > 0 && (
            <section className="pb-[var(--space-lg)]">
              <Container size="wide">
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                  {gallery.media_ids.map((id, i) => {
                    const item = media.get(id);
                    if (!item) return null;
                    return (
                      <Reveal key={id} delay={i * 60}>
                        <div className="aspect-square w-full overflow-hidden bg-[color:var(--pub-paper-tint)]">
                          <img src={item.thumb_url ?? item.url} alt="" loading="lazy" decoding="async" className="h-full w-full object-cover" />
                        </div>
                      </Reveal>
                    );
                  })}
                </div>
              </Container>
            </section>
          )}

          {faq?.items && faq.items.length > 0 && <FaqSection items={faq.items} />}
        </div>
      )}
    </AsyncState>
  );
}

function FaqSection({ items }: { items: FaqItem[] }) {
  return (
    <section className="py-[var(--space-lg)]">
      <Container size="narrow" className="flex flex-col gap-8">
        <Reveal>
          <SectionHeading eyebrow="Questions" title="Frequently Asked" align="left" />
        </Reveal>
        <Reveal delay={90}>
          <Accordion items={items.map((item, i) => ({ id: i, question: item.question, answer: item.answer }))} />
        </Reveal>
      </Container>
    </section>
  );
}
