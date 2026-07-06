import { Link } from "react-router-dom";
import { ChevronRight } from "lucide-react";

export interface BreadcrumbItem {
  label: string;
  to?: string;
}

/**
 * UI/UX Design, Section 5.2 — present on every nested admin screen.
 */
export function Breadcrumb({ items }: { items: BreadcrumbItem[] }) {
  return (
    <nav aria-label="Breadcrumb" className="flex items-center gap-1.5 text-caption text-neutral-500">
      {items.map((item, i) => (
        <span key={item.label} className="flex items-center gap-1.5">
          {item.to ? (
            <Link to={item.to} className="hover:text-navy hover:underline">
              {item.label}
            </Link>
          ) : (
            <span aria-current="page">{item.label}</span>
          )}
          {i < items.length - 1 && <ChevronRight className="h-3 w-3" aria-hidden="true" />}
        </span>
      ))}
    </nav>
  );
}
