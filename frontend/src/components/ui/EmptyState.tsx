import type { LucideIcon } from "lucide-react";
import { Inbox } from "lucide-react";
import type { ReactNode } from "react";

interface EmptyStateProps {
  icon?: LucideIcon;
  title: string;
  description?: string;
  action?: ReactNode;
}

/**
 * Component Library, Section 6.3 — Empty State. Icon + short message +
 * primary action, e.g. "No courses yet — Add your first course."
 */
export function EmptyState({ icon: Icon = Inbox, title, description, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center gap-3 rounded-lg border border-dashed border-[color:var(--color-border)] px-6 py-16 text-center">
      <Icon className="h-10 w-10 text-neutral-400" aria-hidden="true" />
      <p className="text-h4 font-display text-[color:var(--color-text)]">{title}</p>
      {description && <p className="max-w-sm text-body-sm text-neutral-500">{description}</p>}
      {action}
    </div>
  );
}
