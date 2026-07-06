import type { ReactNode } from "react";
import { AlertTriangle } from "lucide-react";
import { Spinner } from "@/components/ui";
import type { ApiError } from "@/types/api";

interface AsyncStateProps {
  isLoading: boolean;
  error: ApiError | null;
  isEmpty?: boolean;
  emptyState?: ReactNode;
  children: ReactNode;
}

/**
 * One wrapper for the loading/error/empty triad every public page needs
 * (explicitly required alongside the real content states) — so each
 * page only has to describe what "empty" looks like for its own data,
 * not re-implement the loading spinner / error card every time.
 */
export function AsyncState({ isLoading, error, isEmpty, emptyState, children }: AsyncStateProps) {
  if (isLoading) {
    return (
      <div className="flex justify-center py-24">
        <Spinner className="h-8 w-8" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center gap-3 rounded-lg border border-dashed border-[color:var(--color-border)] px-6 py-24 text-center">
        <AlertTriangle className="h-10 w-10 text-danger" aria-hidden="true" />
        <p className="text-h4 font-display text-[color:var(--color-text)]">Something went wrong</p>
        <p className="max-w-sm text-body-sm text-neutral-500">{error.message}</p>
      </div>
    );
  }

  if (isEmpty) {
    return <>{emptyState}</>;
  }

  return <>{children}</>;
}
