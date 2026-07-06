/** Matches OfficeHourResource on the backend (Settings module extension). */
export interface OfficeHour {
  day: string;
  is_open: boolean;
  opens_at: string | null;
  closes_at: string | null;
  note: string | null;
  order: number;
}

export type OfficeHourEntry = Pick<OfficeHour, "is_open" | "opens_at" | "closes_at" | "note">;

/** The `day` value itself comes from the database — this only capitalizes it for display. */
export function dayLabel(day: string): string {
  return day.charAt(0).toUpperCase() + day.slice(1);
}
