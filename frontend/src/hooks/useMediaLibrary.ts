import { useCallback, useEffect, useState } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { MediaFolder, MediaItem, MediaTypeFilter } from "@/types/media";

interface UseMediaLibraryOptions {
  folderId?: number | null;
  type?: MediaTypeFilter;
  search?: string;
  /** Set false to skip fetching entirely — e.g. the user lacks media.view. */
  enabled?: boolean;
}

/**
 * Shared data-fetching logic for the Media Library screen and the
 * MediaPickerModal (Development Roadmap, Milestone 1 — "Media Picker
 * modal (reused by every later module)"). Both need the same folder
 * tree, asset grid, and upload behavior; only the presentation differs.
 */
export function useMediaLibrary(options: UseMediaLibraryOptions = {}) {
  const { folderId, type, search, enabled = true } = options;

  const [folders, setFolders] = useState<MediaFolder[]>([]);
  const [media, setMedia] = useState<MediaItem[]>([]);
  const [isLoading, setIsLoading] = useState(enabled);
  const [isUploading, setIsUploading] = useState(false);

  const fetchFolders = useCallback(async () => {
    if (!enabled) return;
    const { data } = await api.get<ApiResponse<MediaFolder[]>>(ENDPOINTS.mediaFolders.list);
    setFolders(data.data);
  }, [enabled]);

  const fetchMedia = useCallback(async () => {
    if (!enabled) return;
    setIsLoading(true);
    try {
      const params: Record<string, string | number> = {};
      if (folderId) params["filter[folder]"] = folderId;
      if (type) params["filter[type]"] = type;
      if (search) params.search = search;

      const { data } = await api.get<ApiCollection<MediaItem>>(ENDPOINTS.media.list, { params });
      setMedia(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [folderId, type, search, enabled]);

  useEffect(() => {
    fetchFolders();
  }, [fetchFolders]);

  useEffect(() => {
    fetchMedia();
  }, [fetchMedia]);

  const upload = useCallback(
    async (file: File, altText: string | null, targetFolderId: number | null) => {
      setIsUploading(true);
      try {
        const form = new FormData();
        form.append("file", file);
        if (altText) form.append("alt_text", altText);
        if (targetFolderId) form.append("folder_id", String(targetFolderId));

        const { data } = await api.post<ApiResponse<MediaItem>>(ENDPOINTS.media.upload, form, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        setMedia((prev) => [data.data, ...prev]);
        return data.data;
      } finally {
        setIsUploading(false);
      }
    },
    []
  );

  const updateMedia = useCallback(async (id: number, payload: { alt_text?: string | null; folder_id?: number | null }) => {
    const { data } = await api.put<ApiResponse<MediaItem>>(ENDPOINTS.media.detail(id), payload);
    setMedia((prev) => prev.map((m) => (m.id === id ? data.data : m)));
    return data.data;
  }, []);

  const deleteMedia = useCallback(async (id: number) => {
    await api.delete(ENDPOINTS.media.detail(id));
    setMedia((prev) => prev.filter((m) => m.id !== id));
  }, []);

  /**
   * Media Library hardening — "Replace files". The response is a *new*
   * record (see MediaController::replace()'s docblock — Spatie has no
   * same-id replace primitive), so this swaps the old entry for the new
   * one in local state rather than merging into it by id.
   */
  const replaceMedia = useCallback(async (id: number, file: File, altText: string | null) => {
    const form = new FormData();
    form.append("file", file);
    if (altText) form.append("alt_text", altText);

    const { data } = await api.post<ApiResponse<MediaItem & { replaced_media_id: number }>>(ENDPOINTS.media.replace(id), form, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    setMedia((prev) => prev.map((m) => (m.id === id ? data.data : m)));
    return data.data;
  }, []);

  const createFolder = useCallback(async (name: string, parentId: number | null) => {
    await api.post(ENDPOINTS.mediaFolders.create, { name, parent_id: parentId });
    await fetchFolders();
  }, [fetchFolders]);

  const deleteFolder = useCallback(async (id: number) => {
    await api.delete(ENDPOINTS.mediaFolders.detail(id));
    await fetchFolders();
  }, [fetchFolders]);

  return {
    folders,
    media,
    isLoading,
    isUploading,
    refetchMedia: fetchMedia,
    upload,
    updateMedia,
    deleteMedia,
    replaceMedia,
    createFolder,
    deleteFolder,
  };
}
