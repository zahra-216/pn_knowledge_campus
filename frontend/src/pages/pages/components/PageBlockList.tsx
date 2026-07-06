import { useState } from "react";
import { ChevronDown, ChevronUp, GripVertical, Pencil, Trash2 } from "lucide-react";
import { cn } from "@/utils/cn";
import { Badge } from "@/components/ui";
import type { PageBlock } from "@/types/page";

interface PageBlockListProps {
  blocks: PageBlock[];
  onEdit: (block: PageBlock) => void;
  onDelete: (block: PageBlock) => void;
  onMove: (id: number, direction: "up" | "down") => void;
  onDrop: (draggedId: number, targetId: number) => void;
}

const BLOCK_TYPE_LABELS: Record<string, string> = {
  hero: "Hero",
  text: "Text",
  rich_text: "Rich Text",
  image: "Image",
  gallery: "Gallery",
  video: "Video",
  cta: "Call to Action",
  faq: "FAQ",
  statistics: "Statistics",
  testimonials: "Testimonials",
  partners: "Partners",
};

/**
 * Page blocks are a flat ordered list (no nesting, unlike menu items —
 * see Database Design's page_blocks.order/page_id index), so this is
 * simpler than MenuItemTree: same native HTML5 drag-and-drop for
 * reordering, plus explicit Move Up/Down buttons, no indent/outdent.
 */
export function PageBlockList({ blocks, onEdit, onDelete, onMove, onDrop }: PageBlockListProps) {
  const [draggingId, setDraggingId] = useState<number | null>(null);

  return (
    <ul className="flex flex-col gap-2">
      {blocks.map((block, index) => (
        <li key={block.id}>
          <div
            draggable
            onDragStart={() => setDraggingId(block.id)}
            onDragOver={(e) => e.preventDefault()}
            onDrop={(e) => {
              e.preventDefault();
              if (draggingId !== null) onDrop(draggingId, block.id);
              setDraggingId(null);
            }}
            className={cn(
              "flex items-center gap-3 rounded-md border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 py-3",
              draggingId === block.id && "opacity-50"
            )}
          >
            <GripVertical className="h-4 w-4 flex-shrink-0 cursor-grab text-neutral-400" aria-hidden="true" />

            <div className="flex flex-1 flex-col overflow-hidden">
              <div className="flex items-center gap-2">
                <span className="text-body-sm font-medium text-[color:var(--color-text)]">
                  {BLOCK_TYPE_LABELS[block.block_type] ?? block.block_type}
                </span>
                {!block.is_active && <Badge tone="neutral">Inactive</Badge>}
              </div>
              <span className="truncate text-caption text-neutral-500">{blockPreview(block)}</span>
            </div>

            <div className="flex flex-shrink-0 items-center gap-0.5">
              <button
                type="button"
                onClick={() => onMove(block.id, "up")}
                disabled={index === 0}
                aria-label="Move up"
                className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5"
              >
                <ChevronUp className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
              <button
                type="button"
                onClick={() => onMove(block.id, "down")}
                disabled={index === blocks.length - 1}
                aria-label="Move down"
                className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5"
              >
                <ChevronDown className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
              <button type="button" onClick={() => onEdit(block)} aria-label="Edit" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5">
                <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
              <button type="button" onClick={() => onDelete(block)} aria-label="Delete" className="rounded p-1.5 text-neutral-500 hover:text-danger">
                <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
            </div>
          </div>
        </li>
      ))}
    </ul>
  );
}

function blockPreview(block: PageBlock): string {
  const data = block.data as Record<string, unknown>;

  switch (block.block_type) {
    case "hero":
    case "cta":
      return (data.heading as string) || "—";
    case "text":
    case "rich_text":
      return (data.body as string) || "—";
    case "image":
      return data.media_id ? `Media #${data.media_id}` : "No image selected";
    case "gallery":
      return `${((data.media_ids as number[]) ?? []).length} image(s)`;
    case "video":
      return (data.url as string) || (data.media_id ? `Media #${data.media_id}` : "—");
    case "faq":
    case "statistics":
    case "testimonials":
    case "partners":
      return `${((data.items as unknown[]) ?? []).length} item(s)`;
    default:
      return "—";
  }
}
