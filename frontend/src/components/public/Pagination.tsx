import { ChevronLeft, ChevronRight } from "lucide-react";
import { cn } from "@/utils/cn";
import type { PaginationMeta } from "@/types/api";

interface PaginationProps {
  meta: PaginationMeta;
  onPageChange: (page: number) => void;
}

/** Numbered pager for every public listing page — shown only when there's more than one page. */
export function Pagination({ meta, onPageChange }: PaginationProps) {
  if (meta.last_page <= 1) return null;

  const pages = Array.from({ length: meta.last_page }, (_, i) => i + 1);

  return (
    <nav aria-label="Pagination" className="mt-10 flex items-center justify-center gap-1.5">
      <button
        type="button"
        disabled={meta.current_page <= 1}
        onClick={() => onPageChange(meta.current_page - 1)}
        className="flex h-9 w-9 items-center justify-center rounded border border-[color:var(--color-border)] text-navy transition-colors hover:bg-navy/5 disabled:cursor-not-allowed disabled:opacity-40 dark:text-white"
        aria-label="Previous page"
      >
        <ChevronLeft className="h-4 w-4" />
      </button>

      {pages.map((page) => (
        <button
          key={page}
          type="button"
          onClick={() => onPageChange(page)}
          aria-current={page === meta.current_page ? "page" : undefined}
          className={cn(
            "flex h-9 w-9 items-center justify-center rounded text-body-sm font-semibold transition-colors",
            page === meta.current_page
              ? "bg-navy text-white"
              : "border border-[color:var(--color-border)] text-navy hover:bg-navy/5 dark:text-white"
          )}
        >
          {page}
        </button>
      ))}

      <button
        type="button"
        disabled={meta.current_page >= meta.last_page}
        onClick={() => onPageChange(meta.current_page + 1)}
        className="flex h-9 w-9 items-center justify-center rounded border border-[color:var(--color-border)] text-navy transition-colors hover:bg-navy/5 disabled:cursor-not-allowed disabled:opacity-40 dark:text-white"
        aria-label="Next page"
      >
        <ChevronRight className="h-4 w-4" />
      </button>
    </nav>
  );
}
