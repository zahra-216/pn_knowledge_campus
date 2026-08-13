import { useState } from "react";
import { Outlet } from "react-router-dom";
import { Sidebar } from "./Sidebar";
import { TopBar } from "./TopBar";
import { cn } from "@/utils/cn";

/** SSR-safe: matches jsdom/test environments where `window` exists but layout hasn't happened yet. */
function isDesktopViewport(): boolean {
  return typeof window !== "undefined" && window.matchMedia("(min-width: 768px)").matches;
}

/**
 * UI/UX Design, Section 5.1 — the structural wireframe, built for real.
 * Every future admin screen (Pages, Courses, News, ...) renders inside
 * the <Outlet /> below via nested routing (see src/routes/AppRoutes.tsx)
 * — none of them need to re-implement the top bar or sidebar.
 *
 * Audit fix (Medium remediation) — below `md`, the sidebar now starts
 * closed and renders as an overlay drawer (backdrop + slide-in panel)
 * instead of the fixed 240px inline column that used to consume most
 * of a 360px viewport on first load. Mirrors the public SiteHeader's
 * own mobile drawer pattern, including closing on navigation.
 */
export function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(isDesktopViewport);

  return (
    <div className="flex h-screen overflow-hidden bg-[color:var(--color-surface-alt)]">
      <div className={cn("hidden transition-all duration-250 md:block", sidebarOpen ? "md:w-60" : "md:w-0 md:overflow-hidden")}>
        <Sidebar />
      </div>

      {sidebarOpen && (
        <div className="fixed inset-0 z-50 md:hidden">
          <div className="absolute inset-0 bg-black/40" onClick={() => setSidebarOpen(false)} />
          <div className="absolute inset-y-0 left-0 w-60 shadow-3">
            <Sidebar onNavigate={() => setSidebarOpen(false)} />
          </div>
        </div>
      )}

      <div className="flex flex-1 flex-col overflow-hidden">
        <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
        <main className="flex-1 overflow-y-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
