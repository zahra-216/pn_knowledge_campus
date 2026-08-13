import { useEffect, useState } from "react";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { cn } from "@/utils/cn";
import { SmartLink } from "@/components/public/SmartLink";
import { Container } from "@/components/public/Container";
import { Reveal } from "@/components/public/Reveal";
import type { HeroSlide } from "@/types/homepage";

const AUTO_ADVANCE_MS = 6000;

/**
 * Public Site Redesign, Stage 1 — the homepage's above-the-fold statement.
 * Same CMS data (Hero Slide Builder) and the same "no slides configured"
 * empty behaviour as the old carousel — presentation changed to one
 * full-bleed, undimmed image with an asymmetric headline set low-left
 * instead of centered, and a scrim confined to the text's own corner
 * instead of a wash over the whole photo. Auto-advances every 6s when an
 * editor has configured more than one slide (feedback: a carousel that
 * only moves on a manual click didn't read as "running"), pausing while
 * the pointer is over it and restarting its countdown from zero on any
 * manual prev/next/dot interaction so it doesn't jump right after.
 */
export function HeroSlider({ slides }: { slides: HeroSlide[] }) {
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const hasMultiple = slides.length > 1;

  useEffect(() => {
    if (!hasMultiple || paused) return;
    const timer = setTimeout(() => setIndex((i) => (i + 1) % slides.length), AUTO_ADVANCE_MS);
    return () => clearTimeout(timer);
  }, [index, paused, hasMultiple, slides.length]);

  if (slides.length === 0) return null;

  const slide = slides[index];

  return (
    <section
      className="relative w-full overflow-hidden bg-[color:var(--pub-ink)] min-h-[clamp(560px,88vh,880px)]"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      {slide.image_url && (
        <img
          key={slide.id}
          src={slide.image_url}
          alt=""
          aria-hidden="true"
          className="absolute inset-0 h-full w-full object-cover animate-pub-fade-in"
        />
      )}
      <div className="pointer-events-none absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/85 via-black/25 to-transparent" />

      <div className="relative flex min-h-[clamp(560px,88vh,880px)] flex-col justify-end">
        <Container size="wide" className="flex flex-col gap-8 pb-14 pt-24 sm:pb-20">
          <Reveal key={slide.id} className="flex flex-col gap-5">
            <h1 className="max-w-4xl text-balance font-display text-hero font-medium text-white">{slide.title}</h1>
            {slide.subtitle && <p className="max-w-xl text-body-lg text-white/80">{slide.subtitle}</p>}
            {slide.cta_url && slide.cta_text && (
              <SmartLink
                to={slide.cta_url}
                className="mt-2 inline-flex h-12 w-fit items-center rounded-sm bg-gold px-7 text-body-sm font-semibold text-navy-dark transition-colors duration-200 hover:bg-gold-tint"
              >
                {slide.cta_text}
              </SmartLink>
            )}
          </Reveal>

          {hasMultiple && (
            <div className="flex items-center gap-4 text-white/70">
              <button
                type="button"
                onClick={() => setIndex((i) => (i - 1 + slides.length) % slides.length)}
                aria-label="Previous slide"
                className="rounded-full p-2 transition-colors hover:bg-white/10 hover:text-white"
              >
                <ArrowLeft className="h-4 w-4" />
              </button>
              <span className="font-sans text-caption tabular-nums tracking-widest">
                {String(index + 1).padStart(2, "0")} / {String(slides.length).padStart(2, "0")}
              </span>
              <button
                type="button"
                onClick={() => setIndex((i) => (i + 1) % slides.length)}
                aria-label="Next slide"
                className="rounded-full p-2 transition-colors hover:bg-white/10 hover:text-white"
              >
                <ArrowRight className="h-4 w-4" />
              </button>
              <div className="ml-2 flex gap-1.5">
                {slides.map((s, i) => (
                  <button
                    key={s.id}
                    type="button"
                    onClick={() => setIndex(i)}
                    aria-label={`Go to slide ${i + 1}`}
                    aria-current={i === index}
                    className={cn("h-[3px] w-6 rounded-full transition-colors", i === index ? "bg-gold" : "bg-white/25 hover:bg-white/40")}
                  />
                ))}
              </div>
            </div>
          )}
        </Container>
      </div>
    </section>
  );
}
