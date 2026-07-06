import { Eye, FileText, Film, Image as ImageIcon } from "lucide-react";
import { cn } from "@/utils/cn";
import { Spinner, EmptyState } from "@/components/ui";
import type { MediaItem } from "@/types/media";

interface MediaGridProps {
  media: MediaItem[];
  isLoading: boolean;
  selectedId?: number | null;
  onSelect: (item: MediaItem) => void;
  /** Optional — omit to hide the preview affordance (e.g. inside the Media Picker). */
  onPreview?: (item: MediaItem) => void;
}

function iconFor(mimeType: string | null) {
  if (mimeType?.startsWith("image/")) return ImageIcon;
  if (mimeType?.startsWith("video/")) return Film;
  return FileText;
}

/**
 * Component Library, Section 6.3/6.4 — the asset grid half of the Media
 * Library screen and the Media Picker modal (both consume this same
 * component, per Development Roadmap Milestone 1).
 */
export function MediaGrid({ media, isLoading, selectedId, onSelect, onPreview }: MediaGridProps) {
  if (isLoading) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  if (media.length === 0) {
    return <EmptyState title="No files yet" description="Upload your first file to get started." />;
  }

  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
      {media.map((item) => {
        const Icon = iconFor(item.mime_type);
        const isImage = item.mime_type?.startsWith("image/");

        return (
          <div
            key={item.id}
            className={cn(
              "group relative flex flex-col overflow-hidden rounded-md border transition-shadow hover:shadow-2",
              selectedId === item.id ? "border-gold ring-2 ring-gold/40" : "border-[color:var(--color-border)]"
            )}
          >
            <button type="button" onClick={() => onSelect(item)} className="flex flex-col text-left">
              <div className="flex aspect-square items-center justify-center bg-[color:var(--color-surface-alt)]">
                {isImage ? (
                  <img src={item.thumb_url ?? item.url} alt={item.alt_text ?? ""} className="h-full w-full object-cover" />
                ) : (
                  <Icon className="h-10 w-10 text-neutral-400" aria-hidden="true" />
                )}
              </div>
              <div className="truncate px-2 py-1.5 text-caption text-[color:var(--color-text)]">{item.name}</div>
            </button>

            {onPreview && (
              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  onPreview(item);
                }}
                aria-label={`Preview ${item.name}`}
                className="absolute right-1.5 top-1.5 hidden rounded bg-black/60 p-1.5 text-white hover:bg-black/80 group-hover:block"
              >
                <Eye className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
            )}
          </div>
        );
      })}
    </div>
  );
}
