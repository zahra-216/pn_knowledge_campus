import type { HTMLAttributes } from "react";
import { cn } from "@/utils/cn";

/**
 * Component Library, Section 6.3 (Content Card / Stat Card share this
 * base shell). Elevation level 1 by default per Design System §7.4.
 */
export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        "rounded-lg border border-[color:var(--color-border)] bg-[color:var(--color-surface)] p-6 shadow-1",
        className
      )}
      {...props}
    />
  );
}
