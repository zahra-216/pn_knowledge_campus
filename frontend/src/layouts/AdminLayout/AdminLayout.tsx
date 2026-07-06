import { useState } from "react";
import { Outlet } from "react-router-dom";
import { Sidebar } from "./Sidebar";
import { TopBar } from "./TopBar";
import { cn } from "@/utils/cn";

/**
 * UI/UX Design, Section 5.1 — the structural wireframe, built for real.
 * Every future admin screen (Pages, Courses, News, ...) renders inside
 * the <Outlet /> below via nested routing (see src/routes/AppRoutes.tsx)
 * — none of them need to re-implement the top bar or sidebar.
 */
export function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  return (
    <div className="flex h-screen overflow-hidden bg-[color:var(--color-surface-alt)]">
      <div className={cn("transition-all duration-250", sidebarOpen ? "w-60" : "w-0 overflow-hidden")}>
        <Sidebar />
      </div>

      <div className="flex flex-1 flex-col overflow-hidden">
        <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
        <main className="flex-1 overflow-y-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
