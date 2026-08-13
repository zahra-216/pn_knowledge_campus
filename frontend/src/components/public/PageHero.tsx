import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Container } from "@/components/public/Container";

interface PageHeroProps {
  imageUrl?: string;
  heading: string;
  subheading?: string | null;
  breadcrumbLabel: string;
}

/**
 * The shared opening statement for every hand-coded inner page (About,
 * Admissions, How to Apply, ...) — a smaller, page-scale relative of
 * HeroSlider's full-bleed homepage treatment (same ink background,
 * gradient, serif heading), not the homepage's own 88vh moment. The
 * breadcrumb sits above the image on the plain paper background rather
 * than overlaid on it, so it stays legible against any photo without
 * needing its own dark-mode variant.
 */
export function PageHero({ imageUrl, heading, subheading, breadcrumbLabel }: PageHeroProps) {
  return (
    <>
      <div className="border-b border-[color:var(--pub-line)] py-4">
        <Container size="wide">
          <Breadcrumb items={[{ label: breadcrumbLabel }]} />
        </Container>
      </div>
      <section className="relative w-full overflow-hidden bg-[color:var(--pub-ink)] min-h-[clamp(320px,42vh,480px)]">
        {imageUrl && <img src={imageUrl} alt="" aria-hidden="true" className="absolute inset-0 h-full w-full object-cover" />}
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-black/10" />
        <div className="relative flex min-h-[clamp(320px,42vh,480px)] flex-col justify-end">
          <Container size="wide" className="flex flex-col gap-3 pb-12 pt-16 text-white">
            <h1 className="max-w-3xl text-balance font-display text-hero font-medium">{heading}</h1>
            {subheading && <p className="max-w-xl text-body-lg text-white/80">{subheading}</p>}
          </Container>
        </div>
      </section>
    </>
  );
}
