import type { HTMLAttributes } from "react";
import { cn } from "@/utils/cn";

export type BadgeTone = "neutral" | "success" | "warning" | "danger" | "info";

interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
  tone?: BadgeTone;
}

/**
 * Component Library, Section 6.3 — Status Badge / Pill. Same shape and
 * size everywhere; only the fill color changes by status (Draft/grey,
 * Published/green, Scheduled/amber, Archived/slate, New/blue, ...).
 * Callers pass a `tone`, never a raw color class.
 */
export function Badge({ tone = "neutral", className, ...props }: BadgeProps) {
  return (
    <span
      className={cn(
        "inline-flex items-center rounded-full px-2.5 py-0.5 text-caption font-medium",
        tone === "neutral" && "bg-neutral-100 text-neutral-700",
        tone === "success" && "bg-success/10 text-success",
        tone === "warning" && "bg-warning/10 text-warning",
        tone === "danger" && "bg-danger/10 text-danger",
        tone === "info" && "bg-info/10 text-info",
        className
      )}
      {...props}
    />
  );
}
