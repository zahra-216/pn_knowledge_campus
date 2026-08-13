import { Outlet, useLocation } from "react-router-dom";
import { useEffect } from "react";
import { SiteHeader } from "@/components/public/SiteHeader";
import { SiteFooter } from "@/components/public/SiteFooter";
import { usePageViewTracking } from "@/hooks/usePageViewTracking";
import { PublicSettingsProvider, usePublicSettings } from "@/hooks/usePublicSettings";
import { useAnalytics } from "@/hooks/useAnalytics";

/**
 * Shell for every public route — header/footer stay mounted across
 * navigations (only <Outlet/> swaps), matching the admin layout's own
 * shell/content split. Scrolls to top on each route change since a
 * SPA doesn't get that behavior for free like a full page load would.
 * Also pings the page-view tracker (Milestone 24) on every navigation,
 * and injects GA/GTM (audit fix, High remediation — see useAnalytics.ts)
 * once per page load if the site has configured either.
 */
export function PublicLayout() {
  return (
    <PublicSettingsProvider>
      <PublicLayoutShell />
    </PublicSettingsProvider>
  );
}

function PublicLayoutShell() {
  const location = useLocation();
  usePageViewTracking();
  const { settings } = usePublicSettings();
  useAnalytics(settings.ga_tracking_id as string | undefined, settings.gtm_container_id as string | undefined);

  useEffect(() => {
    window.scrollTo({ top: 0 });
  }, [location.pathname]);

  return (
    <div className="flex min-h-screen flex-col bg-[color:var(--pub-paper)] text-[color:var(--pub-ink)] antialiased dark:text-white">
      <SiteHeader />
      <main className="flex-1">
        <Outlet />
      </main>
      <SiteFooter />
    </div>
  );
}
