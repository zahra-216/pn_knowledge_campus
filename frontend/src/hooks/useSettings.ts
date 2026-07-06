import { useCallback, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { Setting, SettingsMap, SettingValue } from "@/types/settings";

/**
 * Fetches every setting once and exposes it as a flat { key: value } map
 * for form binding, plus a `save` that only ever sends the keys the
 * caller changed (Section 5.4's tabbed editor saves one section at a
 * time — API Design, Section 9.5's "PUT is a partial update" note).
 */
export function useSettings() {
  const [settings, setSettings] = useState<SettingsMap>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  const fetchSettings = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<Setting[]>>(ENDPOINTS.settings.admin);
      const map: SettingsMap = {};
      for (const setting of data.data) {
        map[setting.key] = setting.value;
      }
      setSettings(map);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchSettings();
  }, [fetchSettings]);

  const save = useCallback(async (changed: Record<string, SettingValue>) => {
    setIsSaving(true);
    try {
      const stringified: Record<string, string> = {};
      for (const [key, value] of Object.entries(changed)) {
        if (value === null || value === undefined) {
          stringified[key] = "";
        } else if (Array.isArray(value)) {
          stringified[key] = JSON.stringify(value);
        } else {
          stringified[key] = String(value);
        }
      }

      const { data } = await api.put<ApiResponse<Setting[]>>(ENDPOINTS.settings.admin, { settings: stringified });
      const map: SettingsMap = {};
      for (const setting of data.data) {
        map[setting.key] = setting.value;
      }
      setSettings(map);
    } finally {
      setIsSaving(false);
    }
  }, []);

  return { settings, isLoading, isSaving, save };
}
