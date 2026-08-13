import type { HTMLAttributes } from "react";
import { cn } from "@/utils/cn";

const SIZE_CLASS = {
  narrow: "max-w-3xl",
  default: "max-w-7xl",
  wide: "max-w-[92rem]",
  full: "max-w-none",
} as const;

interface ContainerProps extends HTMLAttributes<HTMLDivElement> {
  size?: keyof typeof SIZE_CLASS;
}

/**
 * Public Site Redesign, Stage 1 — one width scale for the whole public
 * shell, replacing the old habit of every section picking its own
 * max-w-7xl/6xl/5xl/2xl ad hoc (see the Phase A audit's Homepage
 * finding). `narrow` is a reading measure (article-width prose),
 * `default` is the standard section width, `wide` is for the 12-column
 * editorial grid sections, `full` opts out entirely for full-bleed art.
 */
export function Container({ size = "default", className, ...props }: ContainerProps) {
  return <div className={cn("mx-auto w-full px-4 sm:px-6 lg:px-8", SIZE_CLASS[size], className)} {...props} />;
}
