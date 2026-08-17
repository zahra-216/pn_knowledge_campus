import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { SettingsMap } from "@/types/settings";
import type { Menu } from "@/types/menu";
import type { SocialLink } from "@/types/socialLink";
import { getCached } from "@/lib/requestCache";

interface PublicBootstrapValue {
  settings: SettingsMap;
  headerMenu: Menu | null;
  footerMenu: Menu | null;
  socialLinks: SocialLink[];
  isLoading: boolean;
}

interface BootstrapResponse {
  settings: SettingsMap;
  header_menu: Menu | null;
  footer_menu: Menu | null;
  social_links: SocialLink[];
}

const PublicSettingsContext = createContext<PublicBootstrapValue | null>(null);

/**
 * Fetches GET /public/bootstrap exactly once per page load and shares
 * settings + header menu + footer menu + social links — these were
 * previously 4 separate requests (settings/public, menus/header,
 * menus/footer, social-links), each independently fetched by
 * PublicLayout/SiteHeader/SiteFooter. Mounted once at PublicLayout,
 * above everything that consumes it.
 */
export function PublicSettingsProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<Omit<PublicBootstrapValue, "isLoading">>({
    settings: {},
    headerMenu: null,
    footerMenu: null,
    socialLinks: [],
  });
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    getCached("public-bootstrap", () =>
      api.get<ApiResponse<BootstrapResponse>>(ENDPOINTS.publicBootstrap.get)
    )
      .then(({ data }) => {
        setState({
          settings: data.data.settings,
          headerMenu: data.data.header_menu,
          footerMenu: data.data.footer_menu,
          socialLinks: data.data.social_links,
        });
      })
      .finally(() => setIsLoading(false));
  }, []);

  return (
    <PublicSettingsContext.Provider value={{ ...state, isLoading }}>
      {children}
    </PublicSettingsContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components -- co-locating the hook with its Provider is the standard Context pattern
export function usePublicSettings(): PublicBootstrapValue {
  const ctx = useContext(PublicSettingsContext);
  if (!ctx) {
    throw new Error("usePublicSettings must be used within a PublicSettingsProvider");
  }
  return ctx;
}