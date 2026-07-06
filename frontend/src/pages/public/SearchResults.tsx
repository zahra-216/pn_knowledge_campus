import { useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { ArrowRight, ImageOff } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { SearchResultType, SearchResults as SearchResultsData } from "@/types/search";
import { SEARCH_TYPE_LABEL } from "@/types/search";

/** "View all N" for a type links back to that module's own already-paginated `?search=` listing (see the backend SearchController's docblock — this page deliberately doesn't reimplement per-type pagination). */
const VIEW_ALL_PATH: Record<SearchResultType, string> = {
  course: "/courses",
  page: "/", // Pages have no listing page of their own — no "view all" link is shown for this type.
  blog: "/blog",
  news: "/news",
  event: "/events",
};

export function SearchResults() {
  const [searchParams] = useSearchParams();
  const query = searchParams.get("q") ?? "";
  const [activeTypes, setActiveTypes] = useState<SearchResultType[] | null>(null);

  const {
    data,
    isLoading,
    error,
  } = usePublicDetail<{ query: string; results: SearchResultsData }>(query.trim().length >= 2 ? ENDPOINTS.search.results(query) : null);

  useSeoHead({ title: query ? `Search: ${query}` : "Search", canonicalPath: "/search" });

  const types = useMemo(() => Object.keys(data?.results ?? {}) as SearchResultType[], [data]);
  const visibleTypes = activeTypes ?? types;
  const totalResults = types.reduce((sum, type) => sum + (data?.results[type]?.total ?? 0), 0);

  function toggleType(type: SearchResultType) {
    setActiveTypes((current) => {
      const base = current ?? types;
      return base.includes(type) ? base.filter((t) => t !== type) : [...base, type];
    });
  }

  return (
    <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Search" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">
        {query ? `Search results for "${query}"` : "Search"}
      </h1>

      {query.trim().length < 2 ? (
        <p className="mt-6 text-body text-neutral-500">Enter at least 2 characters to search.</p>
      ) : (
        <AsyncState isLoading={isLoading} error={error}>
          {data && (
            <>
              <p className="mt-2 text-body-sm text-neutral-500">{totalResults} result{totalResults === 1 ? "" : "s"} found</p>

              {types.length > 0 && (
                <div className="mt-6 flex flex-wrap gap-2">
                  {types.map((type) => (
                    <button
                      key={type}
                      type="button"
                      onClick={() => toggleType(type)}
                      className={`rounded-full border px-4 py-1.5 text-body-sm font-medium transition-colors ${
                        visibleTypes.includes(type)
                          ? "border-navy bg-navy text-white"
                          : "border-[color:var(--color-border)] text-neutral-600 hover:bg-navy/5"
                      }`}
                    >
                      {SEARCH_TYPE_LABEL[type]} ({data.results[type]?.total ?? 0})
                    </button>
                  ))}
                </div>
              )}

              <div className="mt-8 flex flex-col gap-10">
                {totalResults === 0 ? (
                  <EmptyState title="No results found" description="Try a different search term." />
                ) : (
                  visibleTypes.map((type) => {
                    const group = data.results[type];
                    if (!group || group.items.length === 0) return null;

                    return (
                      <section key={type}>
                        <div className="flex items-center justify-between">
                          <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">{SEARCH_TYPE_LABEL[type]}</h2>
                          {type !== "page" && group.total > group.items.length && (
                            <Link
                              to={`${VIEW_ALL_PATH[type]}?search=${encodeURIComponent(query)}`}
                              className="flex items-center gap-1 text-body-sm font-semibold text-navy hover:underline dark:text-gold"
                            >
                              View all {group.total} <ArrowRight className="h-4 w-4" />
                            </Link>
                          )}
                        </div>

                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                          {group.items.map((item, i) => (
                            <Link
                              key={i}
                              to={item.url}
                              className="flex gap-3 rounded-lg border border-[color:var(--color-border)] p-4 transition-shadow hover:shadow-2"
                            >
                              <div className="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded bg-neutral-100 dark:bg-white/5">
                                {item.image_url ? (
                                  <img src={item.image_url} alt="" loading="lazy" decoding="async" className="h-full w-full object-cover" />
                                ) : (
                                  <ImageOff className="h-6 w-6 text-neutral-400" />
                                )}
                              </div>
                              <div>
                                <p className="font-semibold text-[color:var(--color-text)]">{item.title}</p>
                                {item.excerpt && <p className="mt-1 line-clamp-2 text-body-sm text-neutral-500">{item.excerpt}</p>}
                              </div>
                            </Link>
                          ))}
                        </div>
                      </section>
                    );
                  })
                )}
              </div>
            </>
          )}
        </AsyncState>
      )}
    </div>
  );
}
