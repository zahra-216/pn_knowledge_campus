import { useCallback, useEffect, useState } from "react";

type Theme = "light" | "dark";

const THEME_KEY = "pnkc_theme";

function getInitialTheme(): Theme {
  const stored = localStorage.getItem(THEME_KEY);
  if (stored === "light" || stored === "dark") return stored;

  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

/**
 * Dark mode toggle with persistence (UI/UX Design, Section 9.4 / 11.1).
 * Applies/removes the `dark` class on <html>, which is all
 * tailwind.config.ts's `darkMode: "class"` needs to flip every color
 * token defined in src/styles/index.css.
 */
export function useTheme() {
  const [theme, setTheme] = useState<Theme>(getInitialTheme);

  useEffect(() => {
    document.documentElement.classList.toggle("dark", theme === "dark");
    localStorage.setItem(THEME_KEY, theme);
  }, [theme]);

  const toggleTheme = useCallback(() => {
    setTheme((prev) => (prev === "dark" ? "light" : "dark"));
  }, []);

  return { theme, toggleTheme };
}
