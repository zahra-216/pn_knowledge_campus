import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { ApiError, ApiResponse } from "@/types/api";

/**
 * One hook backs every public detail page (Course/Blog/News/Event/
 * Faculty/Department/Gallery Album/Static Page) — a single-resource
 * `GET /{module}/{slug}` lookup. `endpoint` may be `null` while a route
 * param hasn't resolved yet; the fetch is skipped until it's a string.
 */
export function usePublicDetail<T>(endpoint: string | null) {
  const [data, setData] = useState<T | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<ApiError | null>(null);

  useEffect(() => {
    if (!endpoint) return;
    let cancelled = false;
    setIsLoading(true);
    setError(null);
    setData(null);

    api
      .get<ApiResponse<T>>(endpoint)
      .then(({ data }) => {
        if (!cancelled) setData(data.data);
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
  }, [endpoint]);

  return { data, isLoading, error };
}
