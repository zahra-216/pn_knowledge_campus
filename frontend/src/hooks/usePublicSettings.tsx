import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { Setting, SettingsMap } from "@/types/settings";

interface PublicSettingsValue {
  settings: SettingsMap;
  isLoading: boolean;
}

const PublicSettingsContext = createContext<PublicSettingsValue | null>(null);

/**
 * Fetches GET /settings/public exactly once per page load and shares
 * the result — SiteHeader, SiteFooter, PublicLayout, and every page
 * that reads settings (Home, Contact, ...) used to each independently
 * call this hook, so a single page load fired the same request 4-5
 * times over. Mounted once at PublicLayout, above everything that
 * consumes it.
 */
export function PublicSettingsProvider({ children }: { children: ReactNode }) {
  const [settings, setSettings] = useState<SettingsMap>({});
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api
      .get<ApiResponse<Setting[]>>(ENDPOINTS.settings.public)
      .then(({ data }) => {
        const map: SettingsMap = {};
        for (const setting of data.data) {
          map[setting.key] = setting.value;
        }
        setSettings(map);
      })
      .finally(() => setIsLoading(false));
  }, []);

  return <PublicSettingsContext.Provider value={{ settings, isLoading }}>{children}</PublicSettingsContext.Provider>;
}

/**
 * Read-only counterpart to the admin's useSettings() hook — same
 * {settings, isLoading} shape as before, now backed by shared context
 * instead of its own fetch. Must be used under <PublicSettingsProvider>
 * (mounted in PublicLayout) — every current caller already renders
 * inside that tree.
 */
// eslint-disable-next-line react-refresh/only-export-components -- co-locating the hook with its Provider is the standard Context pattern
export function usePublicSettings(): PublicSettingsValue {
  const ctx = useContext(PublicSettingsContext);
  if (!ctx) {
    throw new Error("usePublicSettings must be used within a PublicSettingsProvider");
  }
  return ctx;
}
