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
 * Audit fix (High remediation) — this used to render `error.message`
 * verbatim, so a visitor who'd merely browsed a few pages and tripped
 * the API rate limiter saw the raw backend string "Too Many Attempts."
 * with no explanation or next step. Maps the status codes a visitor can
 * actually hit into plain copy; anything unrecognized gets a generic
 * message rather than whatever text happened to come back from the API.
 */
function friendlyErrorMessage(error: ApiError): string {
  if (error.status === 429) return "You're browsing a little fast — please wait a moment and try again.";
  if (error.status && error.status >= 500) return "Something went wrong on our end. Please try again shortly.";
  if (error.status === 404) return "We couldn't find what you were looking for.";
  return "Something went wrong loading this page. Please try again.";
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
        <p className="max-w-sm text-body-sm text-neutral-500">{friendlyErrorMessage(error)}</p>
      </div>
    );
  }

  if (isEmpty) {
    return <>{emptyState}</>;
  }

  return <>{children}</>;
}
