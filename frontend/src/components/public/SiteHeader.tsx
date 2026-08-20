import { useEffect, useRef, useState } from "react";
import { Link, NavLink, useLocation } from "react-router-dom";
import { Menu as MenuIcon, X, ChevronDown } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicList } from "@/hooks/usePublicList";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import { SearchBox } from "@/components/public/SearchBox";
import { Container } from "@/components/public/Container";
import type { MenuItem } from "@/types/menu";
import type { Faculty } from "@/types/faculty";
import type { Department } from "@/types/department";
import type { Course, CourseCategory, CourseLookup } from "@/types/course";

type MegaMenuLeaf = { id: string; label: string; url: string };

interface DynamicMenuData {
  faculties: Faculty[];
  departments: Department[];
  categories: CourseCategory[];
  levels: CourseLookup[];
  courses: Course[];
}

function flattenCategories(categories: CourseCategory[]): CourseCategory[] {
  return categories.flatMap((c) => [c, ...(c.children ?? [])]);
}

function resolveDynamicChildren(child: MenuItem, data: DynamicMenuData): MegaMenuLeaf[] | null {
  if (child.custom_url === "/faculties") {
    return data.faculties.map((f) => ({ id: `faculty-${f.id}`, label: f.name, url: `/faculties/${f.slug}` }));
  }
  if (child.custom_url === "/departments") {
    return data.departments.map((d) => ({ id: `department-${d.id}`, label: d.name, url: `/departments/${d.slug}` }));
  }
  if (child.custom_url === "/courses") {
    return flattenCategories(data.categories).map((c) => ({
      id: `category-${c.id}`,
      label: c.name,
      url: `/courses?category=${c.slug}`,
    }));
  }
  const levelMatch = child.custom_url?.match(/^\/courses\?level=([^&]+)$/);
  if (levelMatch) {
    const level = data.levels.find((l) => l.slug === levelMatch[1]);
    if (!level) return [];
    return data.courses
      .filter((course) => course.level.name === level.name)
      .map((course) => ({ id: `course-${course.id}`, label: course.course_name, url: `/courses/${course.slug}` }));
  }
  return null;
}

function isMenuItemActive(item: MenuItem, pathname: string): boolean {
  if (item.url && (pathname === item.url || pathname.startsWith(`${item.url}/`))) return true;
  return item.children.some((child) => isMenuItemActive(child, pathname));
}

export function SiteHeader() {
  const { settings, isLoading: settingsLoading, headerMenu: menu } = usePublicSettings();
  const logoMediaId = settings.logo_media_id != null ? Number(settings.logo_media_id) : undefined;
  const resolvedMedia = useResolvedMedia([logoMediaId]);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  const [megaMenuNeeded, setMegaMenuNeeded] = useState(false);
  const { data: allFaculties } = usePublicDetail<Faculty[]>(megaMenuNeeded ? ENDPOINTS.faculties.publicList : null);
  const { data: allDepartments } = usePublicDetail<Department[]>(
    megaMenuNeeded ? ENDPOINTS.departments.publicList : null,
  );
  const { data: allCategories } = usePublicDetail<CourseCategory[]>(
    megaMenuNeeded ? ENDPOINTS.courseCategories.public : null,
  );
  const { data: allLevels } = usePublicDetail<CourseLookup[]>(megaMenuNeeded ? ENDPOINTS.courseLevels.public : null);
  const { items: allCourses } = usePublicList<Course>(
    megaMenuNeeded ? ENDPOINTS.courses.publicList : null,
    { per_page: 100 },
  );
  const dynamicMenuData: DynamicMenuData = {
    faculties: allFaculties ?? [],
    departments: allDepartments ?? [],
    categories: allCategories ?? [],
    levels: allLevels ?? [],
    courses: allCourses,
  };

  useEffect(() => {
    function onScroll() {
      setScrolled(window.scrollY > 8);
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    if (!mobileOpen) return;
    setMegaMenuNeeded(true); // mobile drawer shows the same dynamic columns, so it needs the data too
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = previousOverflow;
    };
  }, [mobileOpen]);

  const logo = resolvedMedia.get(logoMediaId as number);
  const brandStillLoading = settingsLoading || (!!logoMediaId && !logo);
  const campusName = (settings.campus_short_name as string) || (settings.campus_name as string) || "PNK Global Campus";
  const headerLogoHeight = Number(settings.header_logo_height) || 56;
  const headerLogoHeightScrolled = Math.round(headerLogoHeight * 0.78);
  const items = menu?.items ?? [];
  const desktopItems = items.filter((item) => item.visible_on !== "mobile");
  const mobileItems = items.filter((item) => item.visible_on !== "desktop");

  return (
    <>
      <header
        className={cn(
          "sticky top-0 z-40 bg-[color:var(--pub-paper)] transition-shadow duration-300",
          scrolled ? "shadow-[0_1px_0_0_var(--pub-line)]" : "shadow-none"
        )}
      >
        <Container
          size="wide"
          className={cn("flex items-stretch justify-between gap-6 transition-[height] duration-300", scrolled ? "h-16" : "h-20")}
        >
          <Link
            to="/"
            className="flex flex-none items-center gap-2.5 font-display text-h4 font-medium text-[color:var(--pub-ink)] dark:text-white"
          >
            {brandStillLoading ? (
              <span
                aria-hidden="true"
                className="w-28 animate-pulse rounded-sm bg-[color:var(--pub-paper-tint)] transition-[height] duration-300"
                style={{ height: scrolled ? headerLogoHeightScrolled : headerLogoHeight }}
              />
            ) : logo ? (
              // An uploaded logo is assumed to already carry the campus's
              // name/mark visually — pairing it with the text wordmark too
              // reads as a duplicate brand name, so the text is reserved
              // for the no-logo-configured fallback only.
              <img
                src={logo.thumb_url ?? logo.url}
                alt={campusName}
                className="object-contain transition-[height] duration-300"
                style={{ height: scrolled ? headerLogoHeightScrolled : headerLogoHeight, width: "auto" }}
              />
            ) : (
              <>
                <span className="flex h-9 w-9 items-center justify-center rounded-sm bg-[color:var(--pub-ink)] text-white">
                  {campusName.charAt(0)}
                </span>
                <span className="hidden sm:inline">{campusName}</span>
              </>
            )}
          </Link>

          <nav
            className="hidden items-center gap-1 lg:flex"
            aria-label="Primary"
            onMouseEnter={() => setMegaMenuNeeded(true)}
            onFocus={() => setMegaMenuNeeded(true)}
          >
            {desktopItems.map((item) => (
              <HeaderNavItem key={item.id} item={item} scrolled={scrolled} dynamicMenuData={dynamicMenuData} />
            ))}
          </nav>

          <div className="flex items-center gap-1 lg:hidden">
            <SearchBox />
            <button
              type="button"
              onClick={() => setMobileOpen(true)}
              aria-label="Open menu"
              className="rounded-sm p-2 text-[color:var(--pub-ink)] hover:bg-[color:var(--pub-paper-tint)] dark:text-white"
            >
              <MenuIcon className="h-6 w-6" />
            </button>
          </div>
        </Container>
      </header>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-black/50 animate-pub-fade-in" onClick={() => setMobileOpen(false)} />
          <div className="absolute inset-y-0 right-0 flex w-full max-w-md flex-col overflow-y-auto bg-[color:var(--pub-paper)] shadow-3 animate-pub-panel-in">
            <div className="flex items-center justify-between border-b border-[color:var(--pub-line)] px-6 py-5">
              <span className="font-display text-h4 font-medium text-[color:var(--pub-ink)] dark:text-white">{campusName}</span>
              <button
                type="button"
                onClick={() => setMobileOpen(false)}
                aria-label="Close menu"
                className="rounded-sm p-2 text-[color:var(--pub-ink)] hover:bg-[color:var(--pub-paper-tint)] dark:text-white"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="px-6 pt-5">
              <SearchBox variant="inline" onNavigate={() => setMobileOpen(false)} />
            </div>

            <nav className="flex flex-1 flex-col gap-1 px-3 py-6" aria-label="Mobile">
              {mobileItems.map((item) => (
                <MobileNavItem key={item.id} item={item} onNavigate={() => setMobileOpen(false)} />
              ))}
            </nav>

          </div>
        </div>
      )}
    </>
  );
}

function HeaderNavItem({
  item,
  scrolled,
  dynamicMenuData,
}: {
  item: MenuItem;
  scrolled: boolean;
  dynamicMenuData: DynamicMenuData;
}) {
  const location = useLocation();
  const isItemActive = isMenuItemActive(item, location.pathname);
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  // Tracks *why* the panel is open, so the chevron's click handler can
  // tell a real mouse hover apart from a keyboard/touch activation.
  const hoverOpenRef = useRef(false);
  const hasChildren = item.children.length > 0;
  function handleMouseEnter() {
    hoverOpenRef.current = true;
    setOpen(true);
  }
  function handleMouseLeave() {
    hoverOpenRef.current = false;
    setOpen(false);
  }
  function handleChevronClick() {
    if (hoverOpenRef.current) return;
    setOpen((v) => !v);
  }

  useEffect(() => {
    if (!open) return;

    function handleClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        hoverOpenRef.current = false;
        setOpen(false);
      }
    }
    function handleEscape(e: KeyboardEvent) {
      if (e.key === "Escape") {
        hoverOpenRef.current = false;
        setOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("keydown", handleEscape);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("keydown", handleEscape);
    };
  }, [open]);

  if (!hasChildren) {
    return (
      <NavLink
        to={item.url ?? "#"}
        target={item.open_in_new_tab ? "_blank" : undefined}
        rel={item.open_in_new_tab ? "noopener noreferrer" : undefined}
        className={({ isActive }) =>
          cn(
            "group relative px-3.5 py-2 text-body-sm font-medium text-[color:var(--pub-ink)] dark:text-white/90",
            isActive && "text-gold"
          )
        }
      >
        {({ isActive }) => (
          <>
            {item.label}
            <span
              className={cn(
                "pointer-events-none absolute inset-x-3.5 -bottom-px h-px origin-left scale-x-0 bg-gold transition-transform duration-200 group-hover:scale-x-100",
                isActive && "scale-x-100"
              )}
            />
          </>
        )}
      </NavLink>
    );
  }

  return (
    <div
      ref={containerRef}
      className="relative flex h-full items-center self-stretch"
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
    >
      <NavLink
        to={item.url ?? "#"}
        className={cn(
          "group relative py-2 pl-3.5 pr-1 text-body-sm font-medium text-[color:var(--pub-ink)] dark:text-white/90",
          isItemActive && "text-gold"
        )}
      >
        {item.label}
        <span
          className={cn(
            "pointer-events-none absolute inset-x-3.5 -bottom-px h-px origin-left scale-x-0 bg-gold transition-transform duration-200 group-hover:scale-x-100",
            isItemActive && "scale-x-100"
          )}
        />
      </NavLink>
      <button
        type="button"
        onClick={handleChevronClick}
        aria-expanded={open}
        aria-label={`${open ? "Close" : "Open"} ${item.label} submenu`}
        className="flex items-center py-2 pl-1 pr-3 text-[color:var(--pub-ink)] dark:text-white/90"
      >
        <ChevronDown className={cn("h-3.5 w-3.5 transition-transform duration-200", open && "rotate-180")} />
      </button>
      {open &&
        (item.is_mega_menu ? (
          <div
            className="fixed inset-x-0 animate-pub-rise-in border-b border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] shadow-2 transition-[top] duration-300"
            style={{ top: scrolled ? 64 : 80 }}
          >
            <Container
              size="wide"
              className={cn(
                "grid gap-8 py-8",
                item.children.length >= 5
                  ? "grid-cols-5"
                  : item.children.length === 4
                    ? "grid-cols-4"
                    : item.children.length === 2
                      ? "grid-cols-2"
                      : "grid-cols-3"
              )}
            >
              {item.children.map((child) => {
                const subItems = resolveDynamicChildren(child, dynamicMenuData) ?? child.children;

                return (
                  <div key={child.id}>
                    <Link
                      to={child.url ?? "#"}
                      onClick={() => setOpen(false)}
                      className="block font-display text-body font-medium text-[color:var(--pub-ink)] hover:text-gold dark:text-white"
                    >
                      {child.label}
                    </Link>
                    {child.description && <p className="mt-1.5 text-caption text-[color:var(--pub-muted)]">{child.description}</p>}
                    {subItems.length > 0 && (
                      <ul className="mt-3 flex flex-col gap-2 border-t border-[color:var(--pub-line)] pt-3">
                        {subItems.map((grandchild) => (
                          <li key={grandchild.id}>
                            <Link
                              to={grandchild.url ?? "#"}
                              onClick={() => setOpen(false)}
                              className="block text-body-sm text-[color:var(--pub-muted)] hover:text-[color:var(--pub-ink)] dark:hover:text-white"
                            >
                              {grandchild.label}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                );
              })}
            </Container>
          </div>
        ) : (
          <div className="absolute left-0 top-full min-w-[240px] animate-pub-rise-in border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] py-2 shadow-2">
            {item.children.map((child) => (
              <Link
                key={child.id}
                to={child.url ?? "#"}
                onClick={() => setOpen(false)}
                className="block px-4 py-2.5 text-body-sm text-[color:var(--pub-ink)] hover:bg-[color:var(--pub-paper-tint)] dark:text-white"
              >
                {child.label}
              </Link>
            ))}
          </div>
        ))}
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
        className="rounded-sm px-3 py-3 font-display text-h4 font-normal text-[color:var(--pub-ink)] hover:bg-[color:var(--pub-paper-tint)] dark:text-white"
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
        className="flex w-full items-center justify-between rounded-sm px-3 py-3 font-display text-h4 font-normal text-[color:var(--pub-ink)] hover:bg-[color:var(--pub-paper-tint)] dark:text-white"
        aria-expanded={expanded}
      >
        {item.label}
        <ChevronDown className={cn("h-4 w-4 text-[color:var(--pub-muted)] transition-transform", expanded && "rotate-180")} />
      </button>
      {expanded && (
        <div className="ml-3 flex flex-col gap-1 border-l border-[color:var(--pub-line)] pl-4">
          {item.children.map((child) => (
            <Link
              key={child.id}
              to={child.url ?? "#"}
              onClick={onNavigate}
              className="rounded-sm px-3 py-2 text-body text-[color:var(--pub-muted)] hover:text-[color:var(--pub-ink)] dark:hover:text-white"
            >
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
