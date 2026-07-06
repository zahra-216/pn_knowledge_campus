import { Menu, Search, Moon, Sun } from "lucide-react";
import { NotificationsPanel } from "./NotificationsPanel";
import { ProfileMenu } from "./ProfileMenu";
import { useTheme } from "@/hooks/useTheme";

interface TopBarProps {
  onToggleSidebar: () => void;
}

/**
 * UI/UX Design, Section 5.2 — fixed, 64px height. Global search is a
 * visual placeholder in this foundation build (Cmd/Ctrl+K wiring and
 * real search results land once Section 7.5's search endpoint exists).
 */
export function TopBar({ onToggleSidebar }: TopBarProps) {
  const { theme, toggleTheme } = useTheme();

  return (
    <header className="flex h-16 flex-shrink-0 items-center justify-between border-b border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-4">
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onToggleSidebar}
          aria-label="Toggle sidebar"
          className="rounded p-2 hover:bg-black/5 dark:hover:bg-white/5"
        >
          <Menu className="h-5 w-5 text-navy dark:text-white" aria-hidden="true" />
        </button>

        <label className="relative hidden md:block">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" aria-hidden="true" />
          <input
            type="search"
            placeholder="Search..."
            aria-label="Global search"
            className="h-9 w-64 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface-alt)] pl-9 pr-3 text-body-sm"
            disabled
            title="Ships alongside the modules it searches"
          />
        </label>
      </div>

      <div className="flex items-center gap-1">
        <button
          type="button"
          onClick={toggleTheme}
          aria-label="Toggle dark mode"
          className="rounded p-2 hover:bg-black/5 dark:hover:bg-white/5"
        >
          {theme === "dark" ? (
            <Sun className="h-5 w-5 text-white" aria-hidden="true" />
          ) : (
            <Moon className="h-5 w-5 text-navy" aria-hidden="true" />
          )}
        </button>
        <NotificationsPanel />
        <ProfileMenu />
      </div>
    </header>
  );
}
