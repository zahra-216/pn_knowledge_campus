import { defineConfig } from "vitest/config";
import react from "@vitejs/plugin-react";
import path from "node:path";

/**
 * Audit fix (High remediation) — this project had zero frontend test
 * coverage. Kept as its own config file (not merged into vite.config.ts)
 * so the production build config stays exactly what it was — `test` is
 * not a recognized key there anyway without the `vitest/config` wrapper,
 * and this keeps the two concerns (bundling vs. testing) from drifting
 * into one file that neither a build nor a test run fully needs.
 *
 * The npm "test"/"test:watch" scripts set
 * NODE_OPTIONS=--no-experimental-webstorage — recent Node versions ship
 * their own global `localStorage`, which wins over jsdom's and silently
 * lacks methods like `.clear()`, breaking anything that touches
 * localStorage (this app's own tokenStorage included). Disabling Node's
 * copy lets jsdom's real implementation take over.
 */
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  test: {
    environment: "jsdom",
    setupFiles: ["./src/test/setup.ts"],
    css: false,
  },
});
