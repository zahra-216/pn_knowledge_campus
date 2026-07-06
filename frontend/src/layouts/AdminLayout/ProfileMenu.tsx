import { useState, useRef, useEffect } from "react";
import { ChevronDown, LogOut, UserCircle } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { useNavigate } from "react-router-dom";
import { cn } from "@/utils/cn";

/**
 * UI/UX Design, Section 5.2 — profile menu (avatar, name, role, Profile
 * Settings, Logout) on the far right of the Top Bar.
 */
export function ProfileMenu() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  async function handleLogout() {
    await logout();
    navigate("/login");
  }

  if (!user) return null;

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-expanded={open}
        aria-haspopup="menu"
        className="flex items-center gap-2 rounded px-2 py-1.5 hover:bg-black/5 dark:hover:bg-white/5"
      >
        <UserCircle className="h-6 w-6 text-navy" aria-hidden="true" />
        <span className="hidden text-left sm:block">
          <span className="block text-body-sm font-medium leading-tight">{user.name}</span>
          <span className="block text-caption leading-tight text-neutral-500">{user.role}</span>
        </span>
        <ChevronDown className={cn("h-3.5 w-3.5 transition-transform", open && "rotate-180")} aria-hidden="true" />
      </button>

      {open && (
        <div
          role="menu"
          className="absolute right-0 top-full mt-1 w-48 rounded border border-[color:var(--color-border)] bg-[color:var(--color-surface)] py-1 shadow-2"
        >
          <button
            role="menuitem"
            onClick={handleLogout}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-body-sm text-danger hover:bg-danger/5"
          >
            <LogOut className="h-4 w-4" aria-hidden="true" />
            Log out
          </button>
        </div>
      )}
    </div>
  );
}
