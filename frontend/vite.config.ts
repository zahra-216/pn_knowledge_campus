import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "node:path";

/**
 * Milestone 25 (Performance Optimization). Two changes on top of the
 * defaults:
 *
 * - `manualChunks` pulls the framework (react/react-dom/react-router-dom)
 *   into its own vendor chunk, separate from application code. These
 *   rarely change between deploys, so browsers keep it cached across
 *   releases instead of re-downloading it every time any app code
 *   changes. Everything else (recharts, axios, lucide-react, ...) is
 *   left to Rollup's default chunking — since AppRoutes.tsx now
 *   `import()`s every page lazily, a heavy dependency like recharts
 *   that's only used by one page (Dashboard) already ends up isolated
 *   in that page's own chunk with no manual configuration needed.
 * - `sourcemap: false` is Vite's own production default; set explicitly
 *   here so it reads as a deliberate choice, not an oversight — this is
 *   a public-facing app, and shipping sourcemaps would hand out
 *   original (unminified, commented) source to anyone who asks.
 */
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  server: {
    port: 5173,
  },
  build: {
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks: {
          "vendor-react": ["react", "react-dom", "react-router-dom"],
        },
      },
    },
  },
});
