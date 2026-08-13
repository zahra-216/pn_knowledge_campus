import type { ReactNode } from "react";
import { ArrowRight } from "lucide-react";
import { Link } from "react-router-dom";
import { cn } from "@/utils/cn";

interface SectionHeadingProps {
  eyebrow?: string;
  title: ReactNode;
  lede?: string;
  align?: "left" | "center";
  size?: "md" | "lg";
  tone?: "default" | "inverted";
  viewAllTo?: string;
  viewAllLabel?: string;
  className?: string;
}

/**
 * Public Site Redesign, Stage 1 — the one heading pattern every homepage
 * section uses (eyebrow + serif title + optional lede), replacing the old
 * `text-h2 font-semibold` repeated verbatim on ~6 sections with no
 * variation (Phase A audit, Diagnosis §5). `size="lg"` is reserved for
 * the one or two statement moments per page; everything else is "md".
 */
export function SectionHeading({
  eyebrow,
  title,
  lede,
  align = "left",
  size = "md",
  tone = "default",
  viewAllTo,
  viewAllLabel = "View all",
  className,
}: SectionHeadingProps) {
  return (
    <div
      className={cn(
        "flex flex-col gap-3",
        align === "center" && "items-center text-center",
        viewAllTo && "sm:flex-row sm:items-end sm:justify-between sm:gap-6 sm:text-left",
        className
      )}
    >
      <div className={cn("flex flex-col gap-3", align === "center" && "items-center")}>
        {eyebrow && (
          <p
            className={cn(
              "text-caption font-semibold uppercase tracking-[0.14em]",
              tone === "inverted" ? "text-gold" : "text-gold"
            )}
          >
            {eyebrow}
          </p>
        )}
        <h2
          className={cn(
            "font-display font-medium text-balance",
            size === "lg" ? "text-hero" : "text-h1",
            tone === "inverted" ? "text-white" : "text-[color:var(--pub-ink)] dark:text-white"
          )}
        >
          {title}
        </h2>
        {lede && (
          <p
            className={cn(
              "max-w-2xl text-body-lg",
              tone === "inverted" ? "text-white/75" : "text-[color:var(--pub-muted)]"
            )}
          >
            {lede}
          </p>
        )}
      </div>

      {viewAllTo && (
        <Link
          to={viewAllTo}
          className={cn(
            "group inline-flex flex-none items-center gap-2 text-body-sm font-semibold",
            tone === "inverted" ? "text-white" : "text-[color:var(--pub-ink)] dark:text-white"
          )}
        >
          {viewAllLabel}
          <ArrowRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
        </Link>
      )}
    </div>
  );
}
