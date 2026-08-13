import { useEffect, useRef, useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { Search, X } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import type { ApiResponse } from "@/types/api";
import type { SearchResult } from "@/types/search";
import { SEARCH_TYPE_LABEL } from "@/types/search";

const DEBOUNCE_MS = 250;

interface SearchBoxProps {
  /**
   * "floating" (default) is the header's icon-that-expands behaviour.
   * "inline" renders permanently open and full-width — used inside the
   * Stage 1 mobile navigation panel, where a small icon-trigger would be
   * an awkward tap target inside an already-open drawer.
   */
  variant?: "floating" | "inline";
  onNavigate?: () => void;
}

/**
 * Site-wide search entry point (Milestone 21) — a header icon that
 * expands into an input with a live autocomplete dropdown
 * (GET /search/autocomplete), matching Google/Algolia-style
 * "suggestions while typing" UX. Enter (or the "View all results" row)
 * navigates to the full /search results page instead.
 */
export function SearchBox({ variant = "floating", onNavigate }: SearchBoxProps) {
  const navigate = useNavigate();
  const containerRef = useRef<HTMLDivElement>(null);
  const inline = variant === "inline";
  const [open, setOpen] = useState(inline);
  const [query, setQuery] = useState("");
  const [suggestions, setSuggestions] = useState<SearchResult[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (query.trim().length < 2) {
      setSuggestions([]);
      return;
    }

    setIsLoading(true);
    const timer = setTimeout(() => {
      api
        .get<ApiResponse<{ items: SearchResult[] }>>(ENDPOINTS.search.autocomplete(query.trim()))
        .then(({ data }) => setSuggestions(data.data.items))
        .finally(() => setIsLoading(false));
    }, DEBOUNCE_MS);

    return () => clearTimeout(timer);
  }, [query]);

  useEffect(() => {
    if (inline) return;
    function handleClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [inline]);

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!query.trim()) return;
    if (!inline) setOpen(false);
    navigate(`/search?q=${encodeURIComponent(query.trim())}`);
    onNavigate?.();
  }

  function handleSelect(result: SearchResult) {
    if (!inline) setOpen(false);
    setQuery("");
    navigate(result.url);
    onNavigate?.();
  }

  const suggestionsPanel = query.trim().length >= 2 && (
    <div
      className={cn(
        "z-10 border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] py-2 shadow-2",
        inline ? "mt-2 w-full rounded-sm" : "absolute left-0 top-full mt-2 w-full min-w-[300px] rounded-sm"
      )}
    >
      {isLoading ? (
        <p className="px-4 py-3 text-body-sm text-[color:var(--pub-muted)]">Searching…</p>
      ) : suggestions.length === 0 ? (
        <p className="px-4 py-3 text-body-sm text-[color:var(--pub-muted)]">No matches found.</p>
      ) : (
        <>
          {suggestions.map((result, i) => (
            <button
              key={`${result.type}-${i}`}
              type="button"
              onClick={() => handleSelect(result)}
              className="flex w-full flex-col items-start gap-0.5 px-4 py-2.5 text-left hover:bg-[color:var(--pub-paper-tint)]"
            >
              <span className="text-caption font-semibold uppercase tracking-wide text-gold">{SEARCH_TYPE_LABEL[result.type]}</span>
              <span className="text-body-sm text-[color:var(--pub-ink)] dark:text-white">{result.title}</span>
            </button>
          ))}
          <button
            type="submit"
            onClick={inline ? handleSubmit : undefined}
            className="mt-1 w-full border-t border-[color:var(--pub-line)] px-4 pt-2 text-left text-body-sm font-semibold text-[color:var(--pub-ink)] hover:underline dark:text-white"
          >
            View all results for &ldquo;{query.trim()}&rdquo;
          </button>
        </>
      )}
    </div>
  );

  if (inline) {
    return (
      <div ref={containerRef} className="w-full">
        <form onSubmit={handleSubmit} className="relative">
          <Search className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:var(--pub-muted)]" />
          <input
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search courses, news, events…"
            aria-label="Search"
            className="h-12 w-full rounded-sm border border-[color:var(--pub-line)] bg-[color:var(--pub-paper-tint)] pl-11 pr-4 text-body-sm text-[color:var(--pub-ink)] placeholder:text-[color:var(--pub-muted)] focus:border-gold focus:outline-none dark:text-white"
          />
        </form>
        {suggestionsPanel}
      </div>
    );
  }

  return (
    <div ref={containerRef} className="relative">
      {open ? (
        <form onSubmit={handleSubmit} className="flex items-center animate-pub-rise-in">
          <div className="relative">
            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:var(--pub-muted)]" />
            <input
              autoFocus
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search courses, news, events…"
              className="h-10 w-56 rounded-sm border border-[color:var(--pub-line)] bg-[color:var(--pub-paper)] pl-9 pr-8 text-body-sm text-[color:var(--pub-ink)] focus:border-gold focus:outline-none dark:text-white sm:w-72"
            />
            <button
              type="button"
              onClick={() => {
                setOpen(false);
                setQuery("");
              }}
              aria-label="Close search"
              className="absolute right-2 top-1/2 -translate-y-1/2 text-[color:var(--pub-muted)] hover:text-[color:var(--pub-ink)] dark:hover:text-white"
            >
              <X className="h-4 w-4" />
            </button>
          </div>

          {suggestionsPanel}
        </form>
      ) : (
        <button
          type="button"
          onClick={() => setOpen(true)}
          aria-label="Open search"
          className="rounded-sm p-2 text-[color:var(--pub-ink)] transition-colors hover:bg-[color:var(--pub-paper-tint)] dark:text-white dark:hover:bg-white/10"
        >
          <Search className="h-5 w-5" />
        </button>
      )}
    </div>
  );
}
