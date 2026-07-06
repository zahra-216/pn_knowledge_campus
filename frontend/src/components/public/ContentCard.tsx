import { Link } from "react-router-dom";
import { ImageOff } from "lucide-react";
import { Card } from "@/components/ui/Card";
import { Badge } from "@/components/ui/Badge";

interface ContentCardProps {
  to: string;
  image: string | null;
  title: string;
  meta?: string | null;
  excerpt?: string | null;
  badge?: string | null;
}

/**
 * The one card component every listing page uses (Course/Blog/News/
 * Event) — image + badge + title + meta line + excerpt, per the UI/UX
 * Component Library's Content Card spec. Differences between modules
 * are just which strings get passed in, not a different component.
 */
export function ContentCard({ to, image, title, meta, excerpt, badge }: ContentCardProps) {
  return (
    <Link to={to} className="group block">
      <Card className="flex h-full flex-col overflow-hidden p-0 transition-shadow duration-150 hover:shadow-2">
        <div className="relative aspect-[16/10] w-full overflow-hidden bg-neutral-100 dark:bg-white/5">
          {image ? (
            <img
              src={image}
              alt={title}
              loading="lazy"
              className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
          ) : (
            <div className="flex h-full w-full items-center justify-center text-neutral-400">
              <ImageOff className="h-8 w-8" aria-hidden="true" />
            </div>
          )}
          {badge && (
            <Badge tone="info" className="absolute left-3 top-3 bg-navy text-white">
              {badge}
            </Badge>
          )}
        </div>
        <div className="flex flex-1 flex-col gap-2 p-5">
          {meta && <p className="text-caption font-medium uppercase tracking-wide text-gold">{meta}</p>}
          <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)] group-hover:text-navy dark:group-hover:text-gold">
            {title}
          </h3>
          {excerpt && <p className="line-clamp-3 text-body-sm text-neutral-500">{excerpt}</p>}
        </div>
      </Card>
    </Link>
  );
}
