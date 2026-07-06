import { useState } from "react";
import { ChevronDown, ChevronUp, GripVertical } from "lucide-react";
import { cn } from "@/utils/cn";
import { Switch, Badge } from "@/components/ui";
import type { HomepageSection, HomepageSectionKey } from "@/types/homepage";

interface HomepageSectionListProps {
  sections: HomepageSection[];
  canEdit: boolean;
  onChange: (next: HomepageSection[]) => void;
}

const SECTION_LABELS: Record<HomepageSectionKey, string> = {
  hero: "Hero Slider",
  welcome: "Welcome",
  featured_courses: "Featured Courses",
  faculties: "Faculties",
  why_choose_us: "Why Choose Us",
  statistics: "Statistics",
  testimonials: "Testimonials",
  partners: "Partners",
  latest_news: "Latest News",
  upcoming_events: "Upcoming Events",
  cta: "CTA",
  footer_widgets: "Footer Widgets",
};

/**
 * Sections whose owning module (Courses, Faculties, News, Events)
 * doesn't exist in this codebase yet — admins can still enable/reorder
 * the slot, but the public homepage honestly renders no items for it
 * until that module ships (HomepageController's docblock).
 */
const NOT_YET_BUILT: HomepageSectionKey[] = ["featured_courses", "faculties", "latest_news", "upcoming_events"];

/**
 * Homepage Builder — flat, ordered, always-12-rows list (Database
 * Design's homepage_sections table has no create/delete, only
 * enable/reorder). Same native-HTML5-drag-and-drop pattern as
 * MenuItemTree/PageBlockList; toggling a switch persists immediately,
 * just like a drag, since the reorder endpoint updates order and
 * is_enabled together in one bulk request.
 */
export function HomepageSectionList({ sections, canEdit, onChange }: HomepageSectionListProps) {
  const [draggingId, setDraggingId] = useState<number | null>(null);

  function move(id: number, direction: "up" | "down") {
    const index = sections.findIndex((s) => s.id === id);
    const swapWith = direction === "up" ? index - 1 : index + 1;
    if (swapWith < 0 || swapWith >= sections.length) return;

    const next = [...sections];
    [next[index], next[swapWith]] = [next[swapWith], next[index]];
    onChange(next);
  }

  function drop(draggedId: number, targetId: number) {
    if (draggedId === targetId) return;
    const next = [...sections];
    const from = next.findIndex((s) => s.id === draggedId);
    const to = next.findIndex((s) => s.id === targetId);
    const [moved] = next.splice(from, 1);
    next.splice(to, 0, moved);
    onChange(next);
  }

  function toggle(id: number, isEnabled: boolean) {
    onChange(sections.map((s) => (s.id === id ? { ...s, is_enabled: isEnabled } : s)));
  }

  return (
    <ul className="flex flex-col gap-2">
      {sections.map((section, index) => (
        <li key={section.id}>
          <div
            draggable={canEdit}
            onDragStart={() => setDraggingId(section.id)}
            onDragOver={(e) => e.preventDefault()}
            onDrop={(e) => {
              e.preventDefault();
              if (draggingId !== null) drop(draggingId, section.id);
              setDraggingId(null);
            }}
            className={cn(
              "flex items-center gap-3 rounded-md border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 py-3",
              draggingId === section.id && "opacity-50"
            )}
          >
            <GripVertical className="h-4 w-4 flex-shrink-0 cursor-grab text-neutral-400" aria-hidden="true" />

            <div className="flex flex-1 items-center gap-2">
              <span className="text-body-sm font-medium text-[color:var(--color-text)]">
                {SECTION_LABELS[section.section_key]}
              </span>
              {NOT_YET_BUILT.includes(section.section_key) && (
                <Badge tone="neutral">No content module yet</Badge>
              )}
            </div>

            {canEdit && (
              <div className="flex flex-shrink-0 items-center gap-1">
                <button
                  type="button"
                  onClick={() => move(section.id, "up")}
                  disabled={index === 0}
                  aria-label="Move up"
                  className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5"
                >
                  <ChevronUp className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button
                  type="button"
                  onClick={() => move(section.id, "down")}
                  disabled={index === sections.length - 1}
                  aria-label="Move down"
                  className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5"
                >
                  <ChevronDown className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              </div>
            )}

            <Switch checked={section.is_enabled} onChange={(checked) => toggle(section.id, checked)} disabled={!canEdit} />
          </div>
        </li>
      ))}
    </ul>
  );
}
