import { NavLink } from "react-router-dom";
import {
  LayoutDashboard,
  FileText,
  GraduationCap,
  Newspaper,
  Image,
  Inbox,
  ClipboardList,
  Search,
  Settings,
  Users,
  ChevronDown,
} from "lucide-react";
import { useState } from "react";
import { cn } from "@/utils/cn";

/**
 * Matches the Admin Sitemap (UI/UX Design, Section 2.2) and the Sidebar
 * region of the Dashboard Layout wireframe (Section 5.1/5.2). Groups
 * with children expand in place (accordion), not as flyouts.
 *
 * Every item now has a real route — Inquiries, SEO Manager, and Users &
 * Roles were the last three still scaffolded without one (rendered as
 * disabled buttons); their backend modules and admin screens now exist
 * (Inquiry Management admin inbox, SEO Manager overview, and Users/
 * Roles/Permissions CRUD, per SRS FR-26/FR-29 and the Permission
 * Matrix). A group/child with no `to` still renders disabled rather
 * than 404ing, for whatever's genuinely not built yet.
 */
interface NavItem {
  label: string;
  icon: typeof LayoutDashboard;
  to?: string;
  children?: { label: string; to?: string }[];
}

const NAV_ITEMS: NavItem[] = [
  { label: "Dashboard", icon: LayoutDashboard, to: "/admin" },
  {
    label: "Content",
    icon: FileText,
    children: [
      { label: "Pages", to: "/admin/pages" },
      { label: "Menus", to: "/admin/menus" },
      { label: "Homepage", to: "/admin/homepage" },
      { label: "Hero Slider", to: "/admin/hero-slides" },
    ],
  },
  {
    label: "Academics",
    icon: GraduationCap,
    children: [
      { label: "Faculties", to: "/admin/faculties" },
      { label: "Departments", to: "/admin/departments" },
      { label: "Courses", to: "/admin/courses" },
      { label: "Course Levels", to: "/admin/course-levels" },
      { label: "Course Modes", to: "/admin/course-modes" },
      { label: "Course Categories", to: "/admin/course-categories" },
    ],
  },
  {
    label: "Engagement",
    icon: Newspaper,
    children: [
      { label: "News", to: "/admin/news" },
      { label: "News Categories", to: "/admin/news-categories" },
      { label: "Blog", to: "/admin/blog" },
      { label: "Blog Categories", to: "/admin/blog-categories" },
      { label: "Tags", to: "/admin/tags" },
      { label: "Events", to: "/admin/events" },
      { label: "Gallery", to: "/admin/gallery-albums" },
      { label: "Testimonials", to: "/admin/testimonials" },
      { label: "Partners", to: "/admin/partners" },
      { label: "Partner Categories", to: "/admin/partner-categories" },
      { label: "FAQ", to: "/admin/faqs" },
      { label: "FAQ Categories", to: "/admin/faq-categories" },
      { label: "Downloads", to: "/admin/downloads" },
      { label: "Download Categories", to: "/admin/download-categories" },
    ],
  },
  { label: "Media Library", icon: Image, to: "/admin/media-library" },
  { label: "Applications", icon: ClipboardList, to: "/admin/applications" },
  { label: "Inquiries", icon: Inbox, to: "/admin/inquiries" },
  { label: "SEO Manager", icon: Search, to: "/admin/seo" },
  { label: "Settings", icon: Settings, to: "/admin/settings" },
  {
    label: "Users & Roles",
    icon: Users,
    children: [
      { label: "Users", to: "/admin/users" },
      { label: "Roles & Permissions", to: "/admin/roles" },
    ],
  },
];

export function Sidebar() {
  const [expanded, setExpanded] = useState<string | null>(null);

  return (
    <aside className="flex h-full w-60 flex-shrink-0 flex-col bg-navy-dark text-white">
      <div className="flex h-16 items-center px-6">
        <span className="font-display text-h4 font-semibold text-white">PN Campus</span>
      </div>
      <nav className="flex-1 overflow-y-auto px-3 py-2" aria-label="Admin navigation">
        {NAV_ITEMS.map((item) => {
          const Icon = item.icon;
          const hasChildren = Boolean(item.children?.length);
          const isExpanded = expanded === item.label;

          if (!hasChildren && item.to) {
            return (
              <NavLink
                key={item.label}
                to={item.to}
                end
                className={({ isActive }) =>
                  cn(
                    "flex items-center gap-3 rounded px-3 py-2 text-body-sm transition-colors",
                    isActive ? "border-l-2 border-gold bg-white/5 font-semibold text-gold" : "text-white/80 hover:bg-white/5"
                  )
                }
              >
                <Icon className="h-4 w-4" aria-hidden="true" />
                {item.label}
              </NavLink>
            );
          }

          return (
            <div key={item.label}>
              <button
                type="button"
                onClick={() => setExpanded(isExpanded ? null : item.label)}
                disabled={!hasChildren}
                aria-expanded={isExpanded}
                className="flex w-full items-center gap-3 rounded px-3 py-2 text-left text-body-sm text-white/50 hover:bg-white/5 disabled:cursor-not-allowed"
                title="Ships in a later Development Roadmap milestone"
              >
                <Icon className="h-4 w-4" aria-hidden="true" />
                <span className="flex-1">{item.label}</span>
                {hasChildren && (
                  <ChevronDown className={cn("h-3.5 w-3.5 transition-transform", isExpanded && "rotate-180")} aria-hidden="true" />
                )}
              </button>
              {hasChildren && isExpanded && (
                <div className="ml-7 flex flex-col gap-1 border-l border-white/10 pl-3 py-1">
                  {item.children!.map((child) =>
                    child.to ? (
                      <NavLink
                        key={child.label}
                        to={child.to}
                        className={({ isActive }) =>
                          cn(
                            "py-1 text-caption transition-colors",
                            isActive ? "font-semibold text-gold" : "text-white/70 hover:text-white"
                          )
                        }
                      >
                        {child.label}
                      </NavLink>
                    ) : (
                      <span key={child.label} className="py-1 text-caption text-white/40">
                        {child.label} <span className="italic">— coming soon</span>
                      </span>
                    )
                  )}
                </div>
              )}
            </div>
          );
        })}
      </nav>
    </aside>
  );
}
