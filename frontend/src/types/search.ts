/** Global Search (Milestone 21) — a thin aggregator over each module's own `?search=` endpoint, not a reimplementation of them (see the backend SearchController's docblock). */
export type SearchResultType = "course" | "page" | "blog" | "news" | "event";

export interface SearchResult {
  type: SearchResultType;
  title: string;
  excerpt: string | null;
  url: string;
  image_url: string | null;
}

export interface SearchResultGroup {
  items: SearchResult[];
  total: number;
}

/** Matches GET /search's grouped response — only requested types are present as keys. */
export type SearchResults = Partial<Record<SearchResultType, SearchResultGroup>>;

export const SEARCH_TYPE_LABEL: Record<SearchResultType, string> = {
  course: "Courses",
  page: "Pages",
  blog: "Blog",
  news: "News",
  event: "Events",
};
