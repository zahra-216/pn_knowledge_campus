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

/**
 * Site-wide search entry point (Milestone 21) — a header icon that
 * expands into an input with a live autocomplete dropdown
 * (GET /search/autocomplete), matching Google/Algolia-style
 * "suggestions while typing" UX. Enter (or the "View all results" row)
 * navigates to the full /search results page instead.
 */
export function SearchBox() {
  const navigate = useNavigate();
  const containerRef = useRef<HTMLDivElement>(null);
  const [open, setOpen] = useState(false);
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
    function handleClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!query.trim()) return;
    setOpen(false);
    navigate(`/search?q=${encodeURIComponent(query.trim())}`);
  }

  function handleSelect(result: SearchResult) {
    setOpen(false);
    setQuery("");
    navigate(result.url);
  }

  return (
    <div ref={containerRef} className="relative">
      {open ? (
        <form onSubmit={handleSubmit} className="flex items-center">
          <div className="relative">
            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
            <input
              autoFocus
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search courses, news, events..."
              className="h-10 w-56 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-8 text-body-sm sm:w-72"
            />
            <button
              type="button"
              onClick={() => {
                setOpen(false);
                setQuery("");
              }}
              aria-label="Close search"
              className="absolute right-2 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600"
            >
              <X className="h-4 w-4" />
            </button>
          </div>

          {query.trim().length >= 2 && (
            <div className="absolute left-0 top-full mt-2 w-full min-w-[280px] rounded-lg border border-[color:var(--color-border)] bg-[color:var(--color-surface)] py-2 shadow-2">
              {isLoading ? (
                <p className="px-4 py-3 text-body-sm text-neutral-500">Searching...</p>
              ) : suggestions.length === 0 ? (
                <p className="px-4 py-3 text-body-sm text-neutral-500">No matches found.</p>
              ) : (
                <>
                  {suggestions.map((result, i) => (
                    <button
                      key={`${result.type}-${i}`}
                      type="button"
                      onClick={() => handleSelect(result)}
                      className="flex w-full flex-col items-start gap-0.5 px-4 py-2 text-left hover:bg-navy/5 dark:hover:bg-white/10"
                    >
                      <span className={cn("text-caption font-medium uppercase tracking-wide text-gold")}>{SEARCH_TYPE_LABEL[result.type]}</span>
                      <span className="text-body-sm text-[color:var(--color-text)]">{result.title}</span>
                    </button>
                  ))}
                  <button
                    type="submit"
                    className="mt-1 w-full border-t border-[color:var(--color-border)] px-4 pt-2 text-left text-body-sm font-semibold text-navy hover:underline dark:text-gold"
                  >
                    View all results for "{query.trim()}"
                  </button>
                </>
              )}
            </div>
          )}
        </form>
      ) : (
        <button
          type="button"
          onClick={() => setOpen(true)}
          aria-label="Open search"
          className="rounded p-2 text-navy hover:bg-navy/5 dark:text-white dark:hover:bg-white/10"
        >
          <Search className="h-5 w-5" />
        </button>
      )}
    </div>
  );
}
