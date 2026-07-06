import { Outlet, useLocation } from "react-router-dom";
import { useEffect } from "react";
import { SiteHeader } from "@/components/public/SiteHeader";
import { SiteFooter } from "@/components/public/SiteFooter";
import { usePageViewTracking } from "@/hooks/usePageViewTracking";

/**
 * Shell for every public route — header/footer stay mounted across
 * navigations (only <Outlet/> swaps), matching the admin layout's own
 * shell/content split. Scrolls to top on each route change since a
 * SPA doesn't get that behavior for free like a full page load would.
 * Also pings the page-view tracker (Milestone 24) on every navigation.
 */
export function PublicLayout() {
  const location = useLocation();
  usePageViewTracking();

  useEffect(() => {
    window.scrollTo({ top: 0 });
  }, [location.pathname]);

  return (
    <div className="flex min-h-screen flex-col bg-[color:var(--color-surface-alt)] text-[color:var(--color-text)]">
      <SiteHeader />
      <main className="flex-1">
        <Outlet />
      </main>
      <SiteFooter />
    </div>
  );
}
