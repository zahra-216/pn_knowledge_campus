import type { LucideIcon } from "lucide-react";
import { cn } from "@/utils/cn";

interface ImagePlaceholderProps {
  icon: LucideIcon;
  /** "tint" for light card slots (faculty/course/news/event cards), "ink" for dark/hero contexts. */
  tone?: "tint" | "ink";
  className?: string;
}

/**
 * Homepage Photography Gap — until real campus/student photography is
 * uploaded via the Media Library, every image slot (hero, faculty cards,
 * course cards, news/event cards) would otherwise render as a flat empty
 * box, which reads as "broken" rather than "no photo yet". This renders
 * an intentional branded placeholder instead: a soft gradient, a faint
 * dot-grid texture, and a centered icon in a ringed circle, using only
 * the existing navy/gold/paper tokens. Swap out automatically once a
 * real `image_url`/`banner_url`/etc. is set on the record — every call
 * site already does `image ? <img /> : <ImagePlaceholder />`.
 */
export function ImagePlaceholder({ icon: Icon, tone = "tint", className }: ImagePlaceholderProps) {
  return (
    <div
      className={cn(
        "relative flex h-full w-full items-center justify-center overflow-hidden",
        tone === "ink"
          ? "bg-gradient-to-br from-navy-dark via-[color:var(--pub-ink)] to-navy"
          : "bg-gradient-to-br from-[color:var(--pub-paper-tint)] to-[color:var(--pub-line)]",
        className
      )}
    >
      <div
        className="absolute inset-0"
        style={{
          backgroundImage: `radial-gradient(${tone === "ink" ? "#ffffff" : "#10162c"} 1px, transparent 1px)`,
          backgroundSize: "20px 20px",
          opacity: tone === "ink" ? 0.06 : 0.08,
        }}
        aria-hidden="true"
      />
      <div
        className={cn(
          "relative flex h-12 w-12 items-center justify-center rounded-full border",
          tone === "ink" ? "border-white/15 bg-white/5 text-white/60" : "border-gold/25 bg-white/70 text-gold dark:bg-white/5"
        )}
      >
        <Icon className="h-5 w-5" aria-hidden="true" />
      </div>
    </div>
  );
}