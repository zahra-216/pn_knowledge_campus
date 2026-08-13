import { useEffect, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Search } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import type { ApiResponse } from "@/types/api";
import type { SearchResult } from "@/types/search";
import { SEARCH_TYPE_LABEL } from "@/types/search";

const DEBOUNCE_MS = 250;

/**
 * Audit fix (High remediation) — TopBar's search input used to be a
 * disabled visual placeholder ("Ships alongside the modules it
 * searches" — that milestone has long since shipped). Reuses the same
 * public GET /search/autocomplete the site-wide SearchBox.tsx already
 * calls: it only covers the five published, public-facing content
 * types (Course/Page/BlogPost/News/Event), not admin-only records
 * (Users, Media, Settings) — a genuine admin-scoped index over draft
 * content and CMS records doesn't exist yet and is a larger, separate
 * feature. Selecting a result navigates within this same SPA to that
 * item's public page — useful for jumping straight to "what does this
 * look like live," which is what an admin searching by title usually
 * wants.
 */
export function GlobalSearch() {
  const navigate = useNavigate();
  const containerRef = useRef<HTMLDivElement>(null);
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
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

  function handleSelect(result: SearchResult) {
    setOpen(false);
    setQuery("");
    navigate(result.url);
  }

  return (
    <div ref={containerRef} className="relative hidden md:block">
      <label className="relative block">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" aria-hidden="true" />
        <input
          type="search"
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setOpen(true);
          }}
          onFocus={() => setOpen(true)}
          placeholder="Search courses, pages, news..."
          aria-label="Global search"
          className="h-9 w-64 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface-alt)] pl-9 pr-3 text-body-sm"
        />
      </label>

      {open && query.trim().length >= 2 && (
        <div className="absolute left-0 top-full mt-2 w-full min-w-[280px] rounded-lg border border-[color:var(--color-border)] bg-[color:var(--color-surface)] py-2 shadow-2">
          {isLoading ? (
            <p className="px-4 py-3 text-body-sm text-neutral-500">Searching...</p>
          ) : suggestions.length === 0 ? (
            <p className="px-4 py-3 text-body-sm text-neutral-500">No matches found.</p>
          ) : (
            suggestions.map((result, i) => (
              <button
                key={`${result.type}-${i}`}
                type="button"
                onClick={() => handleSelect(result)}
                className="flex w-full flex-col items-start gap-0.5 px-4 py-2 text-left hover:bg-navy/5 dark:hover:bg-white/10"
              >
                <span className={cn("text-caption font-medium uppercase tracking-wide text-gold")}>{SEARCH_TYPE_LABEL[result.type]}</span>
                <span className="text-body-sm text-[color:var(--color-text)]">{result.title}</span>
              </button>
            ))
          )}
        </div>
      )}
    </div>
  );
}
