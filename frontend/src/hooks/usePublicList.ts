import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { ApiCollection, ApiError, PaginationMeta } from "@/types/api";

export type ListParams = Record<string, string | number | boolean | undefined>;

/**
 * One hook backs every public listing page (Courses/Blog/News/Events/
 * Faculties/Departments/Gallery) — they all hit a paginated public
 * `GET` endpoint and only differ in which endpoint and which filter
 * params they pass. Re-fetches whenever `endpoint` or the serialized
 * `params` change (so changing a filter/page number refetches).
 */
export function usePublicList<T>(endpoint: string, params?: ListParams) {
  const [items, setItems] = useState<T[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<ApiError | null>(null);
  const paramsKey = JSON.stringify(params ?? {});

  useEffect(() => {
    let cancelled = false;
    setIsLoading(true);
    setError(null);

    api
      .get<ApiCollection<T>>(endpoint, { params })
      .then(({ data }) => {
        if (cancelled) return;
        setItems(data.data);
        setMeta(data.meta);
      })
      .catch((err: ApiError) => {
        if (!cancelled) setError(err);
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [endpoint, paramsKey]);

  return { items, meta, isLoading, error };
}
