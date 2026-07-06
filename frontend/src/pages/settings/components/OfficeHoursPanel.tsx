import { useCallback, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Button, Switch, useToast } from "@/components/ui";
import { dayLabel, type OfficeHour } from "@/types/officeHours";
import type { ApiResponse } from "@/types/api";

type DraftMap = Record<string, { is_open: boolean; opens_at: string; closes_at: string; note: string }>;

function toDraft(hours: OfficeHour[]): DraftMap {
  const draft: DraftMap = {};
  for (const hour of hours) {
    draft[hour.day] = {
      is_open: hour.is_open,
      opens_at: hour.opens_at ?? "",
      closes_at: hour.closes_at ?? "",
      note: hour.note ?? "",
    };
  }
  return draft;
}

/**
 * Settings module extension — Office Hours. Always exactly 7 rows,
 * seeded once by OfficeHourSeeder; this panel edits them in place via
 * the same bulk-update pattern as the plain key/value settings tabs.
 */
export function OfficeHoursPanel() {
  const { showToast } = useToast();
  const [hours, setHours] = useState<OfficeHour[]>([]);
  const [draft, setDraft] = useState<DraftMap>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  const fetchHours = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<OfficeHour[]>>(ENDPOINTS.officeHours.admin);
      setHours(data.data);
      setDraft(toDraft(data.data));
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchHours();
  }, [fetchHours]);

  function updateDay(day: string, patch: Partial<DraftMap[string]>) {
    setDraft((prev) => ({ ...prev, [day]: { ...prev[day], ...patch } }));
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      const { data } = await api.put<ApiResponse<OfficeHour[]>>(ENDPOINTS.officeHours.admin, { hours: draft });
      setHours(data.data);
      setDraft(toDraft(data.data));
      showToast("Office hours saved.", "success");
    } catch {
      showToast("Could not save office hours. Check the times entered.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-3">
      <h3 className="text-h4 font-display font-semibold text-[color:var(--color-text)]">Office Hours</h3>

      <div className="flex flex-col divide-y divide-[color:var(--color-border)] rounded-lg border border-[color:var(--color-border)]">
        {hours.map((hour) => {
          const entry = draft[hour.day];
          if (!entry) return null;

          return (
            <div key={hour.day} className="flex flex-wrap items-center gap-3 p-3">
              <span className="w-28 text-body-sm font-medium text-[color:var(--color-text)]">{dayLabel(hour.day)}</span>

              <Switch checked={entry.is_open} onChange={(checked) => updateDay(hour.day, { is_open: checked })} />

              {entry.is_open ? (
                <>
                  <input
                    type="time"
                    value={entry.opens_at}
                    onChange={(e) => updateDay(hour.day, { opens_at: e.target.value })}
                    className="h-9 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-2 text-body-sm"
                  />
                  <span className="text-body-sm text-neutral-500">to</span>
                  <input
                    type="time"
                    value={entry.closes_at}
                    onChange={(e) => updateDay(hour.day, { closes_at: e.target.value })}
                    className="h-9 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-2 text-body-sm"
                  />
                  <input
                    type="text"
                    placeholder="Note (optional)"
                    value={entry.note}
                    onChange={(e) => updateDay(hour.day, { note: e.target.value })}
                    className="h-9 min-w-40 flex-1 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-2 text-body-sm"
                  />
                </>
              ) : (
                <span className="text-body-sm text-neutral-500">Closed</span>
              )}
            </div>
          );
        })}
      </div>

      <Button onClick={handleSave} isLoading={isSaving} className="self-start">
        Save Office Hours
      </Button>
    </div>
  );
}
