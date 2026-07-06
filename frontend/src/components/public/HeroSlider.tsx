import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { cn } from "@/utils/cn";
import type { HeroSlide } from "@/types/homepage";

const AUTO_ROTATE_MS = 6000;

/** Homepage hero — auto-rotating full-bleed slides, sourced from the Hero Slider Builder (already scheduling-aware server-side). */
export function HeroSlider({ slides }: { slides: HeroSlide[] }) {
  const [index, setIndex] = useState(0);

  useEffect(() => {
    if (slides.length <= 1) return;
    const timer = setInterval(() => setIndex((i) => (i + 1) % slides.length), AUTO_ROTATE_MS);
    return () => clearInterval(timer);
  }, [slides.length]);

  if (slides.length === 0) return null;

  const slide = slides[index];

  return (
    <section className="relative h-[70vh] min-h-[440px] w-full overflow-hidden bg-navy text-white">
      {slide.image_url && (
        <img src={slide.image_url} alt="" aria-hidden="true" className="absolute inset-0 h-full w-full object-cover opacity-50" />
      )}
      <div className="absolute inset-0 bg-gradient-to-t from-navy-dark/80 via-navy/40 to-transparent" />

      <div className="relative mx-auto flex h-full max-w-5xl flex-col items-center justify-center gap-5 px-4 text-center">
        <h1 className="font-display text-display font-semibold">{slide.title}</h1>
        {slide.subtitle && <p className="max-w-2xl text-body-lg text-white/85">{slide.subtitle}</p>}
        {slide.cta_url && slide.cta_text && (
          <Link
            to={slide.cta_url}
            className="mt-2 inline-flex h-11 items-center rounded bg-gold px-6 text-body-sm font-semibold text-navy-dark transition-colors hover:bg-gold-tint"
          >
            {slide.cta_text}
          </Link>
        )}
      </div>

      {slides.length > 1 && (
        <>
          <button
            type="button"
            onClick={() => setIndex((i) => (i - 1 + slides.length) % slides.length)}
            aria-label="Previous slide"
            className="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 hover:bg-white/20"
          >
            <ChevronLeft className="h-6 w-6" />
          </button>
          <button
            type="button"
            onClick={() => setIndex((i) => (i + 1) % slides.length)}
            aria-label="Next slide"
            className="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 hover:bg-white/20"
          >
            <ChevronRight className="h-6 w-6" />
          </button>
          <div className="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
            {slides.map((s, i) => (
              <button
                key={s.id}
                type="button"
                onClick={() => setIndex(i)}
                aria-label={`Go to slide ${i + 1}`}
                aria-current={i === index}
                className={cn("h-2 w-2 rounded-full transition-colors", i === index ? "bg-gold" : "bg-white/40")}
              />
            ))}
          </div>
        </>
      )}
    </section>
  );
}
