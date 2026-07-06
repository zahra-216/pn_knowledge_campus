import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { Setting, SettingsMap } from "@/types/settings";

/**
 * Read-only counterpart to the admin's useSettings() hook — hits
 * GET /settings/public (never exposes an is_public=false key, e.g. the
 * smtp group) instead of the Sanctum-gated /admin/settings. Backs the
 * public site's header logo/favicon, footer contact details/social
 * links, and SEO defaults fallback.
 */
export function usePublicSettings() {
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

  return { settings, isLoading };
}
