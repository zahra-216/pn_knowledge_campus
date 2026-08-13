import { Menu, Moon, Sun } from "lucide-react";
import { NotificationsPanel } from "./NotificationsPanel";
import { ProfileMenu } from "./ProfileMenu";
import { GlobalSearch } from "./GlobalSearch";
import { useTheme } from "@/hooks/useTheme";

interface TopBarProps {
  onToggleSidebar: () => void;
}

/** UI/UX Design, Section 5.2 — fixed, 64px height. */
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

        <GlobalSearch />
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
