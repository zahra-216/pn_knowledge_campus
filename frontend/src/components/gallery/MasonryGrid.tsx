import type { ReactNode } from "react";
import { cn } from "@/utils/cn";

interface MasonryGridProps {
  children: ReactNode;
  className?: string;
}

/**
 * Pinterest-style masonry layout for photo/video grids — CSS multi-column
 * (no dependency; Tailwind's columns-* utilities), since images/videos of
 * varying aspect ratios are exactly what CSS columns handle natively.
 * Each child must set `break-inside-avoid` (or use MasonryItem below) so
 * a single card never splits across two columns.
 */
export function MasonryGrid({ children, className }: MasonryGridProps) {
  return <div className={cn("columns-2 gap-4 sm:columns-3 lg:columns-4", className)}>{children}</div>;
}

export function MasonryItem({ children, className }: { children: ReactNode; className?: string }) {
  return <div className={cn("mb-4 break-inside-avoid", className)}>{children}</div>;
}
