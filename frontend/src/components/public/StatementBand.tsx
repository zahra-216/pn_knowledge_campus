import type { ReactNode } from "react";
import { cn } from "@/utils/cn";
import { Container } from "@/components/public/Container";

interface StatementBandProps {
  tone?: "ink" | "tint" | "paper";
  children: ReactNode;
  className?: string;
  containerSize?: "narrow" | "default" | "wide" | "full";
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
}

/**
 * Public Site Redesign, Stage 1 — the full-bleed "one big idea" section
 * shell that replaces both the old small 4-up statistics grid and the
 * generic centered CTA band (Phase A audit, Component strategy). `ink`
 * is the single confident navy moment a page gets; `tint` is a quieter
 * paper-tinted variant for a CTA that shouldn't visually repeat an
 * adjacent ink section on the same page.
 */
export function StatementBand({
  tone = "ink",
  children,
  className,
  containerSize = "default",
  onMouseEnter,
  onMouseLeave,
}: StatementBandProps) {
  return (
    <section
      className={cn(
        "py-[var(--space-md)] px-4 sm:px-6 lg:px-8",
        tone === "ink" && "bg-[color:var(--pub-ink)] text-white",
        tone === "tint" && "bg-[color:var(--pub-paper-tint)] text-[color:var(--pub-ink)] dark:text-white",
        tone === "paper" && "bg-[color:var(--pub-paper)] text-[color:var(--pub-ink)] dark:text-white",
        className
      )}
      onMouseEnter={onMouseEnter}
      onMouseLeave={onMouseLeave}
    >
      <Container size={containerSize}>{children}</Container>
    </section>
  );
}
