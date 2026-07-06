/**
 * Matches SettingResource on the backend. `value` is already cast to its
 * declared type by the API — the frontend never re-parses a raw string.
 * Array values only occur for 'json'-typed keys (e.g. the Homepage
 * Builder's why_choose_us_items) — those are still written back through
 * `save()` as a JSON-stringified string, since the bulk-update endpoint's
 * `settings` map is string-valued end to end.
 */
export type SettingValue = string | number | boolean | Record<string, unknown>[] | null;

export interface Setting {
  key: string;
  value: SettingValue;
  group: string;
  is_public: boolean;
}

export type SettingsMap = Record<string, SettingValue>;
