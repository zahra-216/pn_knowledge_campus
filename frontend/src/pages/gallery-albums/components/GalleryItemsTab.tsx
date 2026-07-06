import { useMemo, useState } from "react";
import { Plus, Trash2, Pencil, PlayCircle } from "lucide-react";
import { Button, Modal, Textarea, useToast } from "@/components/ui";
import { MediaPickerModal } from "@/components/media/MediaPickerModal";
import { MasonryGrid, MasonryItem } from "@/components/gallery/MasonryGrid";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { cn } from "@/utils/cn";
import type { GalleryAlbum, GalleryItem, GalleryItemType } from "@/types/gallery";
import type { MediaItem } from "@/types/media";

interface GalleryItemsTabProps {
  album: GalleryAlbum;
  canEdit: boolean;
  onRefresh: () => Promise<void>;
}

type TypeFilter = "all" | GalleryItemType;

const FILTERS: { key: TypeFilter; label: string }[] = [
  { key: "all", label: "All" },
  { key: "image", label: "Photos" },
  { key: "video", label: "Videos" },
];

/**
 * Images and videos share one collection ('items' — Database Design,
 * Section 4.6), rendered as a masonry grid since they come in varying
 * aspect ratios. The type filter is client-side (the album's items are
 * already loaded in full — no pagination for a single album's gallery).
 */
export function GalleryItemsTab({ album, canEdit, onRefresh }: GalleryItemsTabProps) {
  const { showToast } = useToast();
  const [filter, setFilter] = useState<TypeFilter>("all");
  const [isPickerOpen, setIsPickerOpen] = useState(false);
  const [captionState, setCaptionState] = useState<{ open: boolean; item: GalleryItem | null }>({ open: false, item: null });
  const [caption, setCaption] = useState("");
  const [isSaving, setIsSaving] = useState(false);

  const items = useMemo(() => (filter === "all" ? album.items : album.items.filter((i) => i.type === filter)), [album.items, filter]);

  async function handleAttach(media: MediaItem) {
    try {
      await api.post(ENDPOINTS.galleryAlbums.media(album.id), { items: [{ media_id: media.id }] });
      await onRefresh();
      showToast("Item added to album.", "success");
    } catch {
      showToast("Could not add this item.", "error");
    } finally {
      setIsPickerOpen(false);
    }
  }

  async function handleRemove(item: GalleryItem) {
    await api.delete(ENDPOINTS.galleryAlbums.mediaItem(album.id, item.id));
    await onRefresh();
  }

  function openCaptionEditor(item: GalleryItem) {
    setCaptionState({ open: true, item });
    setCaption(item.caption ?? "");
  }

  async function handleSaveCaption() {
    if (!captionState.item) return;
    setIsSaving(true);
    try {
      await api.put(ENDPOINTS.galleryAlbums.mediaItem(album.id, captionState.item.id), { caption });
      setCaptionState({ open: false, item: null });
      await onRefresh();
    } catch {
      showToast("Could not save this caption.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div className="flex gap-1 rounded-md border border-[color:var(--color-border)] p-1">
          {FILTERS.map((f) => (
            <button
              key={f.key}
              type="button"
              onClick={() => setFilter(f.key)}
              className={cn(
                "rounded px-3 py-1 text-body-sm transition-colors",
                filter === f.key ? "bg-navy text-white" : "text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
              )}
            >
              {f.label}
            </button>
          ))}
        </div>

        {canEdit && (
          <Button onClick={() => setIsPickerOpen(true)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            Add Media
          </Button>
        )}
      </div>

      {items.length === 0 && <p className="text-body-sm text-neutral-500">No items yet.</p>}

      <MasonryGrid>
        {items.map((item) => (
          <MasonryItem key={item.id}>
            <div className="group relative overflow-hidden rounded-md border border-[color:var(--color-border)]">
              {item.type === "video" ? (
                <div className="flex aspect-video w-full items-center justify-center bg-[color:var(--color-surface-alt)]">
                  <PlayCircle className="h-10 w-10 text-neutral-400" aria-hidden="true" />
                </div>
              ) : (
                <img src={item.thumb_url ?? item.url} alt={item.caption ?? ""} className="w-full object-cover" />
              )}

              {item.caption && (
                <p className="truncate bg-black/50 px-2 py-1 text-caption text-white absolute bottom-0 left-0 right-0">{item.caption}</p>
              )}

              {canEdit && (
                <div className="absolute right-1 top-1 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                  <button
                    type="button"
                    onClick={() => openCaptionEditor(item)}
                    aria-label="Edit caption"
                    className="rounded-full bg-black/60 p-1.5 text-white hover:bg-black/80"
                  >
                    <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
                  </button>
                  <button
                    type="button"
                    onClick={() => handleRemove(item)}
                    aria-label="Remove item"
                    className="rounded-full bg-black/60 p-1.5 text-white hover:bg-black/80"
                  >
                    <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                  </button>
                </div>
              )}
            </div>
          </MasonryItem>
        ))}
      </MasonryGrid>

      <MediaPickerModal open={isPickerOpen} onClose={() => setIsPickerOpen(false)} onSelect={handleAttach} />

      <Modal
        open={captionState.open}
        onClose={() => setCaptionState({ open: false, item: null })}
        title="Edit Caption"
        footer={
          <>
            <Button variant="secondary" onClick={() => setCaptionState({ open: false, item: null })}>
              Cancel
            </Button>
            <Button onClick={handleSaveCaption} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <Textarea label="Caption" value={caption} onChange={(e) => setCaption(e.target.value)} rows={2} />
      </Modal>
    </div>
  );
}
