import { afterEach } from "vitest";
import { cleanup } from "@testing-library/react";
import "@testing-library/jest-dom/vitest";

// @testing-library/react's automatic post-test cleanup only registers
// itself when it detects a global `afterEach` — this project doesn't
// enable vitest's `test.globals` (to avoid every test file needing an
// ambient types reference), so it has to be wired up explicitly here
// instead. Without this, elements from one test leak into the next
// test's DOM snapshot.
afterEach(() => {
  cleanup();
});

// jsdom doesn't implement matchMedia — useTheme.ts calls it on mount to
// read the OS dark-mode preference, which would otherwise throw in
// every test that renders a component using it (SiteHeader, TopBar, ...).
if (!window.matchMedia) {
  window.matchMedia = (query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: () => {},
    removeListener: () => {},
    addEventListener: () => {},
    removeEventListener: () => {},
    dispatchEvent: () => false,
  });
}
