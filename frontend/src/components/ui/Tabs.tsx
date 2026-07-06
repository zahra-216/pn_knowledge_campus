import type { ReactNode } from "react";
import { cn } from "@/utils/cn";

export interface TabItem {
  key: string;
  label: string;
}

interface TabsProps {
  items: TabItem[];
  active: string;
  onChange: (key: string) => void;
  children: ReactNode;
}

/**
 * Component Library, Section 6.4 — Tabs. Used by the Settings screen's
 * six groups and (from Milestone 3 onward) the Course/Page editor.
 */
export function Tabs({ items, active, onChange, children }: TabsProps) {
  return (
    <div>
      <div role="tablist" className="flex gap-1 overflow-x-auto border-b border-[color:var(--color-border)]">
        {items.map((item) => (
          <button
            key={item.key}
            type="button"
            role="tab"
            aria-selected={active === item.key}
            onClick={() => onChange(item.key)}
            className={cn(
              "whitespace-nowrap border-b-2 px-4 py-2.5 text-body-sm font-medium transition-colors",
              active === item.key
                ? "border-gold text-[color:var(--color-text)]"
                : "border-transparent text-neutral-500 hover:text-[color:var(--color-text)]"
            )}
          >
            {item.label}
          </button>
        ))}
      </div>
      <div role="tabpanel" className="pt-6">
        {children}
      </div>
    </div>
  );
}
