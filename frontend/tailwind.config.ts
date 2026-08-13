import type { Config } from "tailwindcss";

/**
 * Every value below is taken directly from the UI/UX Design document —
 * Section 7 (Design System), Section 8 (Typography), Section 9 (Color
 * Palette) — rather than Tailwind's defaults, so a developer never has to
 * cross-reference that document while building a screen; the tokens are
 * already here.
 */
export default {
  content: ["./index.html", "./src/**/*.{ts,tsx}"],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: "#1B2A4A", // color-navy-900 — header/footer, primary buttons, sidebar
          light: "#2A3F6B", // color-navy-700 — hover state
          dark: "#0F1830", // color-dark-bg — dark mode app background
        },
        gold: {
          DEFAULT: "#A6812C", // color-gold-500 — accents, dividers, active nav
          tint: "#D9C48F", // color-gold-200 — subtle highlight backgrounds
          dark: "#C9A227", // color-gold-dark — dark mode accent
        },
        success: "#1B6B2E",
        warning: "#8A6D00",
        danger: "#B3261E",
        info: "#2A5DAB",
        // Public Site Redesign, Stage 1 — additive only. Navy/gold above are
        // untouched (Admin CMS depends on them); ember is a new, narrowly-
        // scoped accent reserved for time-bound urgency (deadlines, "live
        // now" states) so it never competes with gold's heritage/premium role.
        ember: { DEFAULT: "#C1502E", tint: "#F3DCCF" },
      },
      fontFamily: {
        // Headings (Display–H3 only, weight 600) per Typography §8.1
        display: ["Fraunces", "Georgia", "serif"],
        // Everything else — body copy, admin UI, buttons, forms
        sans: ["Inter", "system-ui", "sans-serif"],
      },
      fontSize: {
        // [fontSize, { lineHeight }] pairs taken from the Type Scale table (§8.2)
        display: ["48px", { lineHeight: "56px" }],
        h1: ["40px", { lineHeight: "48px" }],
        h2: ["32px", { lineHeight: "40px" }],
        h3: ["24px", { lineHeight: "32px" }],
        h4: ["20px", { lineHeight: "28px" }],
        "body-lg": ["18px", { lineHeight: "28px" }],
        body: ["16px", { lineHeight: "24px" }],
        "body-sm": ["14px", { lineHeight: "20px" }],
        caption: ["12px", { lineHeight: "16px" }],
        // Public Site Redesign, Stage 1 — additive display-scale tokens for
        // the homepage's statement moments (hero headline, stat band
        // numerals). Fluid via clamp() so they scale with viewport instead
        // of jumping at breakpoints. `display`/`h1`-`h4` above are untouched.
        //
        // Revised (feedback: "100% view feels completely zoomed") — the
        // original pure-vw formulas (6vw / 5vw) put the *minimum* size
        // achieved only below ~900px wide and hit their *max* by ~1536px,
        // meaning every ordinary 1280–1920px laptop/desktop screen — the
        // overwhelming majority of real visits — rendered at or within a
        // few px of the ceiling (86–88px hero, 64px stat) permanently, not
        // "fluid" at all in the range that matters. Rebuilt as rem+vw so
        // the preferred value actually moves across that range: hero now
        // reads ~48px at 1280px and ~59px at 1920px (was 77–88px both);
        // stat reads ~37px at 1280px and ~43px at 1920px (was 64px both).
        hero: ["clamp(2.75rem, 1.75rem + 1.6vw, 4rem)", { lineHeight: "1.05", letterSpacing: "-0.01em" }],
        stat: ["clamp(2rem, 1.5rem + 1vw, 3rem)", { lineHeight: "1" }],
      },
      spacing: {
        // Section 7.2 — 4px base unit scale (Tailwind's default 4px scale
        // already matches this 1:1; listed here as documentation, and to
        // add the two named large values used for hero/section padding)
        18: "72px",
        22: "88px",
      },
      borderRadius: {
        sm: "4px", // radius-sm — inputs, checkboxes, small badges
        DEFAULT: "8px", // radius-md — buttons, cards, dropdowns
        lg: "16px", // radius-lg — modals, feature panels
      },
      boxShadow: {
        // Section 7.4 — Elevation levels
        1: "0 1px 2px rgba(0,0,0,0.06)",
        2: "0 4px 12px rgba(0,0,0,0.10)",
        3: "0 12px 32px rgba(0,0,0,0.16)",
      },
      transitionDuration: {
        150: "150ms",
        250: "250ms",
        300: "300ms",
      },
    },
  },
  plugins: [],
} satisfies Config;
