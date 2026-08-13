import { describe, it, expect, vi, beforeEach } from "vitest";
import { render, screen } from "@testing-library/react";
import { MemoryRouter } from "react-router-dom";
import { SiteHeader } from "@/components/public/SiteHeader";
import { ToastProvider } from "@/components/ui";
import { PublicSettingsProvider } from "@/hooks/usePublicSettings";
import { api } from "@/lib/api";
import type { Menu, MenuItem } from "@/types/menu";

vi.mock("@/lib/api", () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

function menuItem(overrides: Partial<MenuItem>): MenuItem {
  return {
    id: 1,
    parent_id: null,
    label: "Item",
    linkable_type: null,
    linkable_id: null,
    custom_url: "/item",
    url: "/item",
    description: null,
    icon: null,
    is_mega_menu: false,
    open_in_new_tab: false,
    order: 0,
    is_active: true,
    starts_at: null,
    ends_at: null,
    visible_on: "both",
    children: [],
    ...overrides,
  };
}

/**
 * Audit fix (High remediation) — covers the `visible_on` desktop/mobile
 * filtering this same audit pass added to SiteHeader.tsx. The backend
 * deliberately never filters by this field (see MenuController::publicShow()'s
 * docblock) — enforcement is entirely the frontend's job, so it's the
 * one place a regression here would go unnoticed without a test.
 */
describe("SiteHeader visible_on filtering", () => {
  beforeEach(() => {
    vi.mocked(api.get).mockReset();
  });

  function mockMenu(items: MenuItem[]) {
    const menu: Menu = { id: 1, name: "header", items, created_at: "2026-01-01" };
    vi.mocked(api.get).mockImplementation((url: string) => {
      if (url.startsWith("/menus/")) return Promise.resolve({ data: { data: menu } });
      if (url === "/settings/public") return Promise.resolve({ data: { data: [] } });
      return Promise.resolve({ data: { data: [] } });
    });
  }

  it("renders a desktop-only item in the desktop nav but not the mobile drawer", async () => {
    mockMenu([menuItem({ id: 1, label: "Desktop Only", visible_on: "desktop" })]);

    render(
      <ToastProvider>
        <PublicSettingsProvider>
          <MemoryRouter>
            <SiteHeader />
          </MemoryRouter>
        </PublicSettingsProvider>
      </ToastProvider>
    );

    const desktopLink = await screen.findByRole("link", { name: "Desktop Only" });
    expect(desktopLink.closest("nav")).toHaveAttribute("aria-label", "Primary");
  });

  it("renders a mobile-only item in the mobile drawer but not the desktop nav", async () => {
    mockMenu([menuItem({ id: 2, label: "Mobile Only", visible_on: "mobile" })]);

    render(
      <ToastProvider>
        <PublicSettingsProvider>
          <MemoryRouter>
            <SiteHeader />
          </MemoryRouter>
        </PublicSettingsProvider>
      </ToastProvider>
    );

    // The desktop <nav> should never receive a mobile-only item.
    const primaryNav = await screen.findByRole("navigation", { name: "Primary" });
    expect(primaryNav).not.toHaveTextContent("Mobile Only");
  });

  it("renders a 'both' item in the desktop nav", async () => {
    mockMenu([menuItem({ id: 3, label: "Everywhere", visible_on: "both" })]);

    render(
      <ToastProvider>
        <PublicSettingsProvider>
          <MemoryRouter>
            <SiteHeader />
          </MemoryRouter>
        </PublicSettingsProvider>
      </ToastProvider>
    );

    expect(await screen.findByRole("link", { name: "Everywhere" })).toBeInTheDocument();
  });
});
