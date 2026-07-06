import type { ReactNode } from "react";
import { Link } from "react-router-dom";
import { ArrowRight } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { HeroSlider } from "@/components/public/HeroSlider";
import { ContentCard } from "@/components/public/ContentCard";
import type { HomepageContentSection, HeroSlide, Testimonial, Partner } from "@/types/homepage";
import type { Faculty } from "@/types/faculty";
import type { Course } from "@/types/course";
import type { NewsArticle } from "@/types/news";
import type { CampusEvent } from "@/types/event";

const SECTION_TITLE: Record<string, string> = {
  faculties: "Our Faculties",
  featured_courses: "Featured Courses",
  latest_news: "Latest News",
  upcoming_events: "Upcoming Events",
};

export function Home() {
  const { data: sections, isLoading, error } = usePublicDetail<HomepageContentSection[]>(ENDPOINTS.homepage.public);
  const { settings } = usePublicSettings();

  const campusName = (settings.campus_name as string) || "PN Knowledge Campus";

  useSeoHead({
    title: campusName,
    description: settings.default_meta_description as string | undefined,
    canonicalPath: "/",
    jsonLd: {
      "@context": "https://schema.org",
      "@type": "WebSite",
      name: campusName,
      url: window.location.origin,
      potentialAction: {
        "@type": "SearchAction",
        target: `${window.location.origin}/search?q={search_term_string}`,
        "query-input": "required name=search_term_string",
      },
    },
  });

  return (
    <AsyncState isLoading={isLoading} error={error}>
      <div className="flex flex-col">
        {(sections ?? []).map((section) => (
          <HomeSection key={section.section_key} section={section} />
        ))}
      </div>
    </AsyncState>
  );
}

function HomeSection({ section }: { section: HomepageContentSection }) {
  switch (section.section_key) {
    case "hero":
      return <HeroSlider slides={(section.items as HeroSlide[]) ?? []} />;

    case "welcome":
      return <WelcomeSection content={section.content ?? {}} />;

    case "faculties":
      return (
        <ListSection title={SECTION_TITLE.faculties}>
          {(section.items as Faculty[]).map((f) => (
            <ContentCard key={f.id} to={`/faculties/${f.slug}`} image={f.banner_url} title={f.name} excerpt={f.short_description} />
          ))}
        </ListSection>
      );

    case "featured_courses":
      return (
        <ListSection title={SECTION_TITLE.featured_courses} tone="alt">
          {(section.items as Course[]).map((c) => (
            <ContentCard
              key={c.id}
              to={`/courses/${c.slug}`}
              image={c.featured_image_url}
              title={c.course_name}
              meta={c.faculty.name}
              excerpt={c.overview}
              badge={c.duration}
            />
          ))}
        </ListSection>
      );

    case "latest_news":
      return (
        <ListSection title={SECTION_TITLE.latest_news} viewAllTo="/news">
          {(section.items as NewsArticle[]).map((n) => (
            <ContentCard
              key={n.id}
              to={`/news/${n.slug}`}
              image={n.featured_image_url}
              title={n.title}
              meta={n.category?.name}
              excerpt={n.excerpt}
            />
          ))}
        </ListSection>
      );

    case "upcoming_events":
      return (
        <ListSection title={SECTION_TITLE.upcoming_events} tone="alt" viewAllTo="/events">
          {(section.items as CampusEvent[]).map((ev) => (
            <ContentCard
              key={ev.id}
              to={`/events/${ev.slug}`}
              image={ev.featured_image_url}
              title={ev.title}
              meta={new Date(ev.starts_at).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" })}
              excerpt={ev.venue}
            />
          ))}
        </ListSection>
      );

    case "testimonials":
      return <TestimonialsSection items={(section.items as Testimonial[]) ?? []} />;

    case "partners":
      return <PartnersSection items={(section.items as Partner[]) ?? []} />;

    case "cta":
      return <CtaSection content={section.content ?? {}} />;

    case "why_choose_us":
      return <WhyChooseUsSection items={(section.items as { icon?: string; title: string; text: string }[]) ?? []} />;

    case "statistics":
      return <StatisticsSection items={(section.items as { label: string; value: string }[]) ?? []} />;

    default:
      return null;
  }
}

function WelcomeSection({ content }: { content: Record<string, unknown> }) {
  const mediaId = content.media_id as number | undefined;
  const media = useResolvedMedia([mediaId]);
  const image = mediaId ? media.get(mediaId) : undefined;

  if (!content.heading && !content.body) return null;

  return (
    <section className="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
      {image && <img src={image.url} alt="" loading="lazy" decoding="async" className="w-full rounded-lg object-cover" />}
      <div>
        {content.heading ? (
          <h2 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{content.heading as string}</h2>
        ) : null}
        {content.body ? <p className="mt-4 whitespace-pre-line text-body text-neutral-500">{content.body as string}</p> : null}
      </div>
    </section>
  );
}

function WhyChooseUsSection({ items }: { items: { icon?: string; title: string; text: string }[] }) {
  if (items.length === 0) return null;
  return (
    <section className="bg-[color:var(--color-surface)] px-4 py-16 sm:px-6 lg:px-8">
      <div className="mx-auto grid max-w-6xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
        {items.map((item, i) => (
          <div key={i} className="flex flex-col items-center gap-3 text-center">
            {item.icon && <span className="text-h1">{item.icon}</span>}
            <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{item.title}</h3>
            <p className="text-body-sm text-neutral-500">{item.text}</p>
          </div>
        ))}
      </div>
    </section>
  );
}

function StatisticsSection({ items }: { items: { label: string; value: string }[] }) {
  if (items.length === 0) return null;
  return (
    <section className="bg-navy px-4 py-14 text-white sm:px-6 lg:px-8">
      <div className="mx-auto grid max-w-5xl grid-cols-2 gap-6 sm:grid-cols-4">
        {items.map((item, i) => (
          <div key={i} className="flex flex-col items-center text-center">
            <span className="font-display text-h1 font-semibold">{item.value}</span>
            <span className="mt-1 text-body-sm text-white/70">{item.label}</span>
          </div>
        ))}
      </div>
    </section>
  );
}

function TestimonialsSection({ items }: { items: Testimonial[] }) {
  if (items.length === 0) return null;
  return (
    <ListSection title="What Our Students Say" columns={2}>
      {items.map((t) => (
        <blockquote key={t.id} className="rounded-lg border border-[color:var(--color-border)] bg-[color:var(--color-surface)] p-6">
          <p className="text-body italic text-[color:var(--color-text)]">&ldquo;{t.content}&rdquo;</p>
          <footer className="mt-4 flex items-center gap-3">
            {t.photo_url && <img src={t.photo_url} alt="" loading="lazy" decoding="async" className="h-10 w-10 rounded-full object-cover" />}
            <div>
              <p className="text-body-sm font-semibold text-[color:var(--color-text)]">{t.name}</p>
              {t.role_title && <p className="text-caption text-neutral-500">{t.role_title}</p>}
            </div>
          </footer>
        </blockquote>
      ))}
    </ListSection>
  );
}

function PartnersSection({ items }: { items: Partner[] }) {
  if (items.length === 0) return null;
  return (
    <section className="px-4 py-14 sm:px-6 lg:px-8">
      <h2 className="mb-8 text-center font-display text-h2 font-semibold text-[color:var(--color-text)]">Our Partners</h2>
      <div className="mx-auto flex max-w-5xl flex-wrap items-center justify-center gap-10">
        {items.map((p) =>
          p.logo_url ? (
            <a key={p.id} href={p.url ?? undefined} target="_blank" rel="noopener noreferrer">
              <img src={p.logo_url} alt={p.name} loading="lazy" decoding="async" className="h-12 w-auto grayscale hover:grayscale-0" />
            </a>
          ) : (
            <span key={p.id} className="text-body-sm text-neutral-500">
              {p.name}
            </span>
          )
        )}
      </div>
    </section>
  );
}

function CtaSection({ content }: { content: Record<string, unknown> }) {
  if (!content.heading) return null;
  return (
    <section className="bg-gold/10 px-4 py-16 text-center sm:px-6 lg:px-8">
      <div className="mx-auto flex max-w-2xl flex-col items-center gap-3">
        <h2 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{content.heading as string}</h2>
        {content.body ? <p className="text-body text-neutral-600">{content.body as string}</p> : null}
        {content.button_url && content.button_label ? (
          <Link
            to={content.button_url as string}
            className="mt-2 inline-flex h-11 items-center rounded bg-navy px-6 text-body-sm font-semibold text-white hover:bg-navy-light"
          >
            {content.button_label as string}
          </Link>
        ) : null}
      </div>
    </section>
  );
}

function ListSection({
  title,
  viewAllTo,
  tone = "default",
  columns = 3,
  children,
}: {
  title?: string;
  viewAllTo?: string;
  tone?: "default" | "alt";
  columns?: 2 | 3;
  children: ReactNode;
}) {
  return (
    <section className={tone === "alt" ? "bg-[color:var(--color-surface)] px-4 py-16 sm:px-6 lg:px-8" : "px-4 py-16 sm:px-6 lg:px-8"}>
      <div className="mx-auto max-w-7xl">
        {title && (
          <div className="mb-8 flex items-center justify-between">
            <h2 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{title}</h2>
            {viewAllTo && (
              <Link to={viewAllTo} className="flex items-center gap-1 text-body-sm font-semibold text-navy hover:underline dark:text-gold">
                View all <ArrowRight className="h-4 w-4" />
              </Link>
            )}
          </div>
        )}
        <div className={`grid gap-6 ${columns === 2 ? "sm:grid-cols-2" : "sm:grid-cols-2 lg:grid-cols-3"}`}>{children}</div>
      </div>
    </section>
  );
}
