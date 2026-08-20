import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, CalendarDays, GraduationCap, Newspaper } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import { AsyncState } from "@/components/public/AsyncState";
import { HeroSlider } from "@/components/public/HeroSlider";
import { SmartLink } from "@/components/public/SmartLink";
import { Container } from "@/components/public/Container";
import { SectionHeading } from "@/components/public/SectionHeading";
import { Reveal } from "@/components/public/Reveal";
import { StatementBand } from "@/components/public/StatementBand";
import { ImagePlaceholder } from "@/components/public/ImagePlaceholder";
import type { HomepageContentSection, HeroSlide, Testimonial, Partner } from "@/types/homepage";
import type { Course } from "@/types/course";
import type { NewsArticle } from "@/types/news";
import type { CampusEvent } from "@/types/event";

function formatDate(value: string | null): string {
  if (!value) return "";
  return new Date(value).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" });
}

function metaLine(parts: (string | null | undefined)[]): string {
  return parts.filter(Boolean).join(" · ");
}

/**
 * Public Site Redesign, Stage 1 — the homepage is the new visual
 * reference standard for the site. Same data contract as before (the
 * Homepage Builder's ordered, independently enabled `sections` list from
 * GET /homepage) and the same `useSeoHead` call — only the presentation
 * per `section_key` changed. Inner listing/detail pages are untouched in
 * this stage, so this file deliberately does not reuse `ContentCard`
 * (shared with Courses/Faculties/Blog/News/Events) — the bespoke sections
 * below are homepage-only.
 */
export function Home() {
  const { data: sections, isLoading, error } = usePublicDetail<HomepageContentSection[]>(ENDPOINTS.homepage.public);
  const { settings } = usePublicSettings();

  const campusName = (settings.campus_name as string) || "PNK Global Campus";

  useSeoHead({
    title: "Home",
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

    case "featured_courses":
      return <ProgrammesSection items={(section.items as Course[]) ?? []} />;

    case "latest_news":
      return <NewsSection items={(section.items as NewsArticle[]) ?? []} />;

    case "upcoming_events":
      return <EventsSection items={(section.items as CampusEvent[]) ?? []} />;

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
  const heading = content.heading as string | undefined;
  const body = content.body as string | undefined;

  if (!heading && !body) return null;

  return (
    <section className="py-[var(--space-md)]">
      <Container size="wide" className="grid gap-12 lg:grid-cols-12 lg:items-center">
        {image && (
          <Reveal className={image ? "min-w-0 lg:col-span-6" : "hidden"}>
            <div className="aspect-[4/3] w-full overflow-hidden bg-[color:var(--pub-paper-tint)]">
              <img src={image.url} alt="" loading="lazy" decoding="async" className="h-full w-full object-cover" />
            </div>
          </Reveal>
        )}
        <Reveal delay={90} className={cn("min-w-0 flex flex-col gap-5", image ? "lg:col-span-6" : "lg:col-span-8")}>
          {heading && <SectionHeading eyebrow="About" title={heading} />}
          {body && <p className="whitespace-pre-line text-body-lg text-[color:var(--pub-muted)]">{body}</p>}
        </Reveal>
      </Container>
    </section>
  );
}

function ProgrammesSection({ items }: { items: Course[] }) {
  if (items.length === 0) return null;

  return (
    <section className="py-[var(--space-md)]">
      <Container size="wide" className="flex flex-col gap-10">
        <Reveal>
          <SectionHeading eyebrow="Programmes" title="Our Courses" viewAllTo="/courses" viewAllLabel="View all courses" />
        </Reveal>

        <div className="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((course, i) => (
            <Reveal key={course.id} delay={i * 60} className="min-w-0">
              <Link to={`/courses/${course.slug}`} className="group flex h-full flex-col">
                <div className="relative aspect-[4/3] w-full overflow-hidden bg-[color:var(--pub-paper-tint)]">
                  {course.featured_image_url ? (
                    <img
                      src={course.featured_image_url}
                      alt=""
                      loading="lazy"
                      decoding="async"
                      className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    />
                  ) : (
                    <ImagePlaceholder icon={GraduationCap} />
                  )}
                </div>
                <div className="mt-5 flex flex-col gap-2">
                  <p className="text-caption font-semibold uppercase tracking-wide text-gold">
                    {metaLine([course.category?.name, course.duration])}
                  </p>
                  <h3 className="font-display text-h4 font-medium text-[color:var(--pub-ink)] group-hover:text-gold dark:text-white">
                    {course.course_name}
                  </h3>
                  <span className="mt-1 inline-flex items-center gap-2 text-body-sm font-semibold text-[color:var(--pub-ink)] dark:text-white">
                    View course <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                  </span>
                </div>
              </Link>
            </Reveal>
          ))}
        </div>
      </Container>
    </section>
  );
}

function NewsSection({ items }: { items: NewsArticle[] }) {
  if (items.length === 0) return null;
  const [lead, ...rest] = items;

  return (
    <section className="py-[var(--space-md)]">
      <Container size="wide" className="flex flex-col gap-10">
        <Reveal>
          <SectionHeading eyebrow="Newsroom" title="Latest News" viewAllTo="/news" />
        </Reveal>

        <div className="grid gap-10 lg:grid-cols-12">
          <Reveal className="min-w-0 lg:col-span-5">
            <Link to={`/news/${lead.slug}`} className="group flex h-full flex-col">
              <div className="relative aspect-[4/3] w-full overflow-hidden bg-[color:var(--pub-paper-tint)]">
                {lead.featured_image_url ? (
                  <img
                    src={lead.featured_image_url}
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                  />
                ) : (
                  <ImagePlaceholder icon={Newspaper} />
                )}
              </div>
              <div className="mt-5 flex flex-col gap-2">
                <p className="text-caption font-semibold uppercase tracking-wide text-gold">
                  {metaLine([formatDate(lead.published_at), lead.category?.name])}
                </p>
                <h3 className="font-display text-h3 font-medium text-[color:var(--pub-ink)] group-hover:text-gold dark:text-white">{lead.title}</h3>
                {lead.excerpt && <p className="line-clamp-2 text-body text-[color:var(--pub-muted)]">{lead.excerpt}</p>}
              </div>
            </Link>
          </Reveal>

          {rest.length > 0 && (
            <Reveal delay={100} className="min-w-0 lg:col-span-7">
              <ul className="flex flex-col divide-y divide-[color:var(--pub-line)] border-t border-[color:var(--pub-line)]">
                {rest.map((n) => (
                  <li key={n.id}>
                    <Link to={`/news/${n.slug}`} className="group flex items-center justify-between gap-6 py-5">
                      <div className="flex min-w-0 flex-col gap-1.5">
                        <p className="text-caption font-medium uppercase tracking-wide text-[color:var(--pub-muted)]">
                          {metaLine([formatDate(n.published_at), n.category?.name])}
                        </p>
                        <p className="truncate font-display text-h4 font-medium text-[color:var(--pub-ink)] group-hover:text-gold dark:text-white">
                          {n.title}
                        </p>
                      </div>
                      <ArrowRight className="h-4 w-4 flex-none text-[color:var(--pub-muted)] transition-transform group-hover:translate-x-1 group-hover:text-gold" />
                    </Link>
                  </li>
                ))}
              </ul>
            </Reveal>
          )}
        </div>
      </Container>
    </section>
  );
}

function EventsSection({ items }: { items: CampusEvent[] }) {
  if (items.length === 0) return null;

  return (
    <section className="bg-[color:var(--pub-paper-tint)] py-[var(--space-md)]">
      <Container size="wide" className="flex flex-col gap-10">
        <Reveal>
          <SectionHeading eyebrow="What's On" title="Upcoming Events" viewAllTo="/events" />
        </Reveal>
        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((ev, i) => {
            const date = new Date(ev.starts_at);
            return (
              <Reveal key={ev.id} delay={i * 70} className="min-w-0">
                <Link to={`/events/${ev.slug}`} className="group flex h-full flex-col">
                  <div className="relative aspect-[16/10] w-full overflow-hidden bg-[color:var(--pub-paper)]">
                    {ev.featured_image_url ? (
                      <img
                        src={ev.featured_image_url}
                        alt=""
                        loading="lazy"
                        decoding="async"
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                      />
                    ) : (
                      <ImagePlaceholder icon={CalendarDays} />
                    )}
                    <div className="absolute left-0 top-0 flex flex-col items-center bg-[color:var(--pub-ink)] px-4 py-2 leading-none text-white">
                      <span className="font-display text-h3 font-medium tabular-nums">{date.getDate()}</span>
                      <span className="mt-0.5 text-caption uppercase tracking-wide text-white/70">
                        {date.toLocaleDateString(undefined, { month: "short" })}
                      </span>
                    </div>
                  </div>
                  <div className="mt-4 flex flex-col gap-1.5">
                    <p className="text-caption font-medium uppercase tracking-wide text-[color:var(--pub-muted)]">
                      {date.toLocaleDateString(undefined, { weekday: "long", hour: "numeric", minute: "2-digit" })}
                    </p>
                    <h3 className="font-display text-h4 font-medium text-[color:var(--pub-ink)] group-hover:text-gold dark:text-white">{ev.title}</h3>
                    {ev.venue && <p className="text-body-sm text-[color:var(--pub-muted)]">{ev.is_online ? "Online" : ev.venue}</p>}
                  </div>
                </Link>
              </Reveal>
            );
          })}
        </div>
      </Container>
    </section>
  );
}

function TestimonialsSection({ items }: { items: Testimonial[] }) {
  const ordered = [...items].sort((a, b) => Number(b.is_featured) - Number(a.is_featured));
  const [rawIndex, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const hasMultiple = ordered.length > 1;

  // Auto-advances every 6s like the hero slider, pausing on hover and
  // restarting its countdown on any manual dot click (see HeroSlider.tsx).
  useEffect(() => {
    if (!hasMultiple || paused) return;
    const timer = setTimeout(() => setIndex((i) => i + 1), 6000);
    return () => clearTimeout(timer);
  }, [rawIndex, paused, hasMultiple]);

  if (ordered.length === 0) return null;
  const index = rawIndex % ordered.length;
  const t = ordered[index];

  return (
    <StatementBand
      tone="tint"
      containerSize="wide"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      <div className="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-10">
        <p className="text-caption font-semibold uppercase tracking-[0.14em] text-gold lg:col-span-2">Student Voices</p>

        <div className="flex flex-col gap-6 lg:col-span-8">
          <blockquote className="text-balance font-display text-h2 font-normal italic text-[color:var(--pub-ink)] dark:text-white">
            &ldquo;{t.content}&rdquo;
          </blockquote>
          <footer className="flex items-center gap-3">
            {t.photo_url && <img src={t.photo_url} alt="" loading="lazy" decoding="async" className="h-12 w-12 rounded-full object-cover" />}
            <div>
              <p className="text-body-sm font-semibold text-[color:var(--pub-ink)] dark:text-white">{t.name}</p>
              {t.role_title && <p className="text-caption text-[color:var(--pub-muted)]">{t.role_title}</p>}
            </div>
          </footer>
        </div>

        {ordered.length > 1 && (
          <div className="flex gap-2 lg:col-span-2 lg:justify-end">
            {ordered.map((item, i) => (
              <button
                key={item.id}
                type="button"
                onClick={() => setIndex(i)}
                aria-label={`Show testimonial from ${item.name}`}
                aria-current={i === index}
                className={cn("h-[3px] w-6 rounded-full transition-colors", i === index ? "bg-gold" : "bg-[color:var(--pub-line)]")}
              />
            ))}
          </div>
        )}
      </div>
    </StatementBand>
  );
}

function PartnersSection({ items }: { items: Partner[] }) {
  if (items.length === 0) return null;

  return (
    <section className="bg-[color:var(--pub-paper-tint)] py-[var(--space-md)]">
      <Container size="wide" className="flex flex-col items-center gap-10">
        <p className="text-caption font-semibold uppercase tracking-[0.14em] text-[color:var(--pub-muted)]">In Partnership With</p>
        <div className="grid w-full grid-cols-2 gap-4 sm:grid-cols-4">
          {items.map((p) =>
            p.logo_url ? (
              <a
                key={p.id}
                href={p.url ?? undefined}
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-24 min-w-0 items-center justify-center rounded-sm border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] px-6 opacity-70 grayscale transition-all duration-300 hover:opacity-100 hover:grayscale-0"
              >
                <img src={p.logo_url} alt={p.name} loading="lazy" decoding="async" className="h-9 w-auto max-w-full object-contain" />
              </a>
            ) : (
              <div
                key={p.id}
                className="flex h-24 min-w-0 items-center justify-center rounded-sm border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] px-4 text-center"
              >
                <span className="font-display text-body font-medium text-[color:var(--pub-ink)] dark:text-white">
                  {p.name}
                </span>
              </div>
            )
          )}
        </div>
      </Container>
    </section>
  );
}

function CtaSection({ content }: { content: Record<string, unknown> }) {
  if (!content.heading) return null;

  return (
    <StatementBand tone="tint" containerSize="narrow">
      <div className="flex flex-col items-center gap-4 text-center">
        <h2 className="text-balance font-display text-h1 font-medium text-[color:var(--pub-ink)] dark:text-white">
          {content.heading as string}
        </h2>
        {content.body ? <p className="max-w-xl text-body-lg text-[color:var(--pub-muted)]">{content.body as string}</p> : null}
        {content.button_url && content.button_label ? (
          <SmartLink
            to={content.button_url as string}
            className="mt-2 inline-flex h-12 items-center rounded-sm bg-gold px-7 text-body-sm font-semibold text-navy-dark transition-colors duration-200 hover:bg-gold-tint"
          >
            {content.button_label as string}
          </SmartLink>
        ) : null}
      </div>
    </StatementBand>
  );
}

function WhyChooseUsSection({ items }: { items: { icon?: string; title: string; text: string }[] }) {
  if (items.length === 0) return null;

  return (
    <section className="py-[var(--space-md)]">
      <Container size="wide">
        <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-x-12">
          {items.map((item, i) => (
            <Reveal key={i} delay={i * 70} className="flex min-w-0 flex-col gap-3 border-t border-[color:var(--pub-line)] pt-6">
              {item.icon && (
                <span className="text-h2" aria-hidden="true">
                  {item.icon}
                </span>
              )}
              <h3 className="font-display text-h4 font-medium text-[color:var(--pub-ink)] dark:text-white">{item.title}</h3>
              <p className="text-body-sm text-[color:var(--pub-muted)]">{item.text}</p>
            </Reveal>
          ))}
        </div>
      </Container>
    </section>
  );
}

function StatisticsSection({ items }: { items: { label: string; value: string }[] }) {
  if (items.length === 0) return null;

  return (
    <StatementBand tone="ink">
      <div className="grid grid-cols-2 gap-10 lg:grid-cols-4">
        {items.map((item, i) => (
          <Reveal key={i} delay={i * 70} className="flex min-w-0 flex-col gap-1">
            <span className="font-display text-stat font-medium tabular-nums">{item.value}</span>
            <span className="text-body-sm text-white/60">{item.label}</span>
          </Reveal>
        ))}
      </div>
    </StatementBand>
  );
}