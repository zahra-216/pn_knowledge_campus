import { Link } from "react-router-dom";
import { ChevronRight, Home } from "lucide-react";

export interface BreadcrumbItem {
  label: string;
  to?: string;
}

/** Shown near the top of every nested public page (listing/detail), always anchored at Home. */
export function Breadcrumb({ items }: { items: BreadcrumbItem[] }) {
  return (
    <nav aria-label="Breadcrumb" className="flex flex-wrap items-center gap-1.5 text-body-sm text-neutral-500 dark:text-neutral-400">
      <Link to="/" className="flex items-center hover:text-navy dark:hover:text-white" aria-label="Home">
        <Home className="h-3.5 w-3.5" />
      </Link>
      {items.map((item) => (
        <span key={item.label} className="flex items-center gap-1.5">
          <ChevronRight className="h-3.5 w-3.5" aria-hidden="true" />
          {item.to ? (
            <Link to={item.to} className="hover:text-navy hover:underline dark:hover:text-white">
              {item.label}
            </Link>
          ) : (
            <span aria-current="page" className="text-[color:var(--color-text)]">
              {item.label}
            </span>
          )}
        </span>
      ))}
    </nav>
  );
}
