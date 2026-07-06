import { useState } from "react";
import { Link, NavLink } from "react-router-dom";
import { Menu as MenuIcon, X, ChevronDown, Moon, Sun } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { useTheme } from "@/hooks/useTheme";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import { Button } from "@/components/ui/Button";
import { EnquireNowModal } from "@/components/public/EnquireNowModal";
import { SearchBox } from "@/components/public/SearchBox";
import type { Menu, MenuItem } from "@/types/menu";

/**
 * Fully menu-driven header (no hardcoded nav links) — reads the same
 * `header` Menu the admin Menu Builder edits, so a content editor's
 * change appears here with no code change. Falls back to the Home
 * link alone if the menu hasn't been seeded/edited yet, rather than
 * failing to render.
 */
export function SiteHeader() {
  const { data: menu } = usePublicDetail<Menu>(ENDPOINTS.menus.public("header"));
  const { settings } = usePublicSettings();
  const resolvedMedia = useResolvedMedia([settings.logo_media_id as number | undefined]);
  const { theme, toggleTheme } = useTheme();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [enquireOpen, setEnquireOpen] = useState(false);

  const logo = resolvedMedia.get(settings.logo_media_id as number);
  const campusName = (settings.campus_short_name as string) || (settings.campus_name as string) || "PN Knowledge Campus";
  const items = menu?.items ?? [];

  return (
    <header className="sticky top-0 z-40 border-b border-[color:var(--color-border)] bg-[color:var(--color-surface)]/95 backdrop-blur">
      <div className="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <Link to="/" className="flex items-center gap-2.5 font-display text-h4 font-semibold text-navy dark:text-white">
          {logo ? (
            <img src={logo.thumb_url ?? logo.url} alt={campusName} className="h-10 w-auto" />
          ) : (
            <span className="flex h-10 w-10 items-center justify-center rounded bg-navy text-white">
              {campusName.charAt(0)}
            </span>
          )}
          <span className="hidden sm:inline">{campusName}</span>
        </Link>

        <nav className="hidden items-center gap-1 lg:flex" aria-label="Primary">
          {items.map((item) => (
            <HeaderNavItem key={item.id} item={item} />
          ))}
        </nav>

        <div className="hidden items-center gap-3 lg:flex">
          <SearchBox />
          <button
            type="button"
            onClick={toggleTheme}
            aria-label={theme === "dark" ? "Switch to light mode" : "Switch to dark mode"}
            className="rounded p-2 text-navy hover:bg-navy/5 dark:text-white dark:hover:bg-white/10"
          >
            {theme === "dark" ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
          </button>
          <Button onClick={() => setEnquireOpen(true)}>Enquire Now</Button>
        </div>

        <div className="flex items-center gap-1 lg:hidden">
          <SearchBox />
          <button
            type="button"
            onClick={() => setMobileOpen(true)}
            aria-label="Open menu"
            className="rounded p-2 text-navy hover:bg-navy/5 dark:text-white"
          >
            <MenuIcon className="h-6 w-6" />
          </button>
        </div>
      </div>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-black/40" onClick={() => setMobileOpen(false)} />
          <div className="absolute inset-y-0 right-0 flex w-full max-w-xs flex-col gap-1 overflow-y-auto bg-[color:var(--color-surface)] p-4 shadow-3">
            <div className="mb-2 flex items-center justify-between">
              <span className="font-display text-h4 font-semibold text-navy dark:text-white">{campusName}</span>
              <button
                type="button"
                onClick={() => setMobileOpen(false)}
                aria-label="Close menu"
                className="rounded p-2 text-navy hover:bg-navy/5 dark:text-white"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            {items.map((item) => (
              <MobileNavItem key={item.id} item={item} onNavigate={() => setMobileOpen(false)} />
            ))}

            <Button
              className="mt-3"
              onClick={() => {
                setMobileOpen(false);
                setEnquireOpen(true);
              }}
            >
              Enquire Now
            </Button>
          </div>
        </div>
      )}

      <EnquireNowModal open={enquireOpen} onClose={() => setEnquireOpen(false)} />
    </header>
  );
}

function HeaderNavItem({ item }: { item: MenuItem }) {
  const [open, setOpen] = useState(false);
  const hasChildren = item.children.length > 0;

  if (!hasChildren) {
    return (
      <NavLink
        to={item.url ?? "#"}
        target={item.open_in_new_tab ? "_blank" : undefined}
        rel={item.open_in_new_tab ? "noopener noreferrer" : undefined}
        className={({ isActive }) =>
          cn(
            "rounded px-3 py-2 text-body-sm font-medium text-navy hover:bg-navy/5 dark:text-white dark:hover:bg-white/10",
            isActive && "text-gold"
          )
        }
      >
        {item.label}
      </NavLink>
    );
  }

  return (
    <div className="relative" onMouseEnter={() => setOpen(true)} onMouseLeave={() => setOpen(false)}>
      <button
        type="button"
        className="flex items-center gap-1 rounded px-3 py-2 text-body-sm font-medium text-navy hover:bg-navy/5 dark:text-white dark:hover:bg-white/10"
        aria-expanded={open}
      >
        {item.label}
        <ChevronDown className="h-3.5 w-3.5" />
      </button>
      {open && (
        <div className="absolute left-0 top-full min-w-[220px] rounded-lg border border-[color:var(--color-border)] bg-[color:var(--color-surface)] py-2 shadow-2">
          {item.children.map((child) => (
            <Link
              key={child.id}
              to={child.url ?? "#"}
              className="block px-4 py-2 text-body-sm text-[color:var(--color-text)] hover:bg-navy/5 dark:hover:bg-white/10"
            >
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}

function MobileNavItem({ item, onNavigate }: { item: MenuItem; onNavigate: () => void }) {
  const [expanded, setExpanded] = useState(false);
  const hasChildren = item.children.length > 0;

  if (!hasChildren) {
    return (
      <Link
        to={item.url ?? "#"}
        onClick={onNavigate}
        className="rounded px-3 py-2.5 text-body font-medium text-[color:var(--color-text)] hover:bg-navy/5 dark:hover:bg-white/10"
      >
        {item.label}
      </Link>
    );
  }

  return (
    <div>
      <button
        type="button"
        onClick={() => setExpanded((v) => !v)}
        className="flex w-full items-center justify-between rounded px-3 py-2.5 text-body font-medium text-[color:var(--color-text)] hover:bg-navy/5 dark:hover:bg-white/10"
        aria-expanded={expanded}
      >
        {item.label}
        <ChevronDown className={cn("h-4 w-4 transition-transform", expanded && "rotate-180")} />
      </button>
      {expanded && (
        <div className="ml-3 flex flex-col border-l border-[color:var(--color-border)] pl-3">
          {item.children.map((child) => (
            <Link
              key={child.id}
              to={child.url ?? "#"}
              onClick={onNavigate}
              className="rounded px-3 py-2 text-body-sm text-neutral-600 hover:bg-navy/5 dark:text-neutral-300 dark:hover:bg-white/10"
            >
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
