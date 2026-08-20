import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { MediaItem } from "@/types/media";

export function useResolvedMedia(ids: (number | string | null | undefined)[]): Map<number, MediaItem> {
  const [resolved, setResolved] = useState<Map<number, MediaItem>>(new Map());
  const numericIds = ids
    .map((id) => (id == null || id === "" ? null : Number(id)))
    .filter((id): id is number => id != null && !Number.isNaN(id));
  const key = [...new Set(numericIds)].sort((a, b) => a - b).join(",");

  useEffect(() => {
    if (!key) {
      setResolved(new Map());
      return;
    }

    const uniqueIds = key.split(",").map(Number);
    api
      .get<ApiResponse<MediaItem[]>>(ENDPOINTS.media.resolve(uniqueIds))
      .then(({ data }) => {
        setResolved(new Map(data.data.map((item) => [item.id, item])));
      })
      .catch(() => setResolved(new Map()));
  }, [key]);

  return resolved;
}