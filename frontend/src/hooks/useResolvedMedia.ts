import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { MediaItem } from "@/types/media";

/**
 * Settings (logo_media_id, favicon_media_id, welcome_media_id,
 * default_og_image_media_id) and PageBlock.data (hero/image/gallery/
 * video blocks) all store bare Media Library ids with no other public
 * way to turn them into a URL — see GET /api/v1/media/resolve's
 * docblock on the backend. This hook resolves a set of ids once and
 * exposes a Map for O(1) lookup by id.
 */
export function useResolvedMedia(ids: (number | null | undefined)[]): Map<number, MediaItem> {
  const [resolved, setResolved] = useState<Map<number, MediaItem>>(new Map());
  const key = ids.filter((id): id is number => Boolean(id)).sort((a, b) => a - b).join(",");

  useEffect(() => {
    if (!key) {
      setResolved(new Map());
      return;
    }

    const uniqueIds = key.split(",").map(Number);
    api.get<ApiResponse<MediaItem[]>>(ENDPOINTS.media.resolve(uniqueIds)).then(({ data }) => {
      setResolved(new Map(data.data.map((item) => [item.id, item])));
    });
  }, [key]);

  return resolved;
}
