import { useState } from "react";
import { ChevronDown, ChevronRight, GripVertical, Indent, Outdent, Pencil, Plus, Trash2 } from "lucide-react";
import { cn } from "@/utils/cn";
import { Badge } from "@/components/ui";
import type { MenuItem } from "@/types/menu";

interface MenuItemTreeProps {
  items: MenuItem[];
  onEdit: (item: MenuItem) => void;
  onDelete: (item: MenuItem) => void;
  onAddChild: (parentId: number) => void;
  onMove: (id: number, direction: "up" | "down") => void;
  onIndent: (id: number) => void;
  onOutdent: (id: number) => void;
  onDrop: (draggedId: number, targetId: number) => void;
  depth?: number;
}

function statusBadges(item: MenuItem) {
  const badges = [];
  if (!item.is_active) badges.push(<Badge key="inactive" tone="neutral">Inactive</Badge>);
  if (item.is_mega_menu) badges.push(<Badge key="mega" tone="info">Mega menu</Badge>);
  if (item.visible_on !== "both") badges.push(<Badge key="device" tone="warning">{item.visible_on} only</Badge>);
  if (item.starts_at || item.ends_at) badges.push(<Badge key="scheduled" tone="warning">Scheduled</Badge>);
  return badges;
}

/**
 * Menu Builder — nested drag-and-drop tree. Dragging reorders within the
 * same level (native HTML5 drag events, no new dependency); Indent/
 * Outdent buttons handle moving an item between levels, which is more
 * reliable than cross-level drag-and-drop with native APIs.
 */
export function MenuItemTree({ items, onEdit, onDelete, onAddChild, onMove, onIndent, onOutdent, onDrop, depth = 0 }: MenuItemTreeProps) {
  const [collapsed, setCollapsed] = useState<Set<number>>(new Set());
  const [draggingId, setDraggingId] = useState<number | null>(null);

  function toggleCollapsed(id: number) {
    setCollapsed((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }

  return (
    <ul className={cn("flex flex-col gap-1", depth > 0 && "ml-6 border-l border-[color:var(--color-border)] pl-3")}>
      {items.map((item, index) => {
        const hasChildren = item.children.length > 0;
        const isCollapsed = collapsed.has(item.id);

        return (
          <li key={item.id}>
            <div
              draggable
              onDragStart={() => setDraggingId(item.id)}
              onDragOver={(e) => e.preventDefault()}
              onDrop={(e) => {
                e.preventDefault();
                if (draggingId !== null) onDrop(draggingId, item.id);
                setDraggingId(null);
              }}
              className={cn(
                "flex items-center gap-2 rounded-md border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-2 py-2",
                draggingId === item.id && "opacity-50"
              )}
            >
              <GripVertical className="h-4 w-4 flex-shrink-0 cursor-grab text-neutral-400" aria-hidden="true" />

              <button
                type="button"
                onClick={() => toggleCollapsed(item.id)}
                className={cn("flex-shrink-0", !hasChildren && "invisible")}
                aria-label={isCollapsed ? "Expand" : "Collapse"}
              >
                {isCollapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
              </button>

              <div className="flex flex-1 flex-col overflow-hidden">
                <div className="flex items-center gap-2">
                  <span className="truncate text-body-sm font-medium text-[color:var(--color-text)]">{item.label}</span>
                  {statusBadges(item)}
                </div>
                <span className="truncate text-caption text-neutral-500">{item.custom_url || item.url || "—"}</span>
              </div>

              <div className="flex flex-shrink-0 items-center gap-0.5">
                <button type="button" onClick={() => onMove(item.id, "up")} disabled={index === 0} aria-label="Move up" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5">
                  <ChevronRight className="h-3.5 w-3.5 -rotate-90" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onMove(item.id, "down")} disabled={index === items.length - 1} aria-label="Move down" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5">
                  <ChevronRight className="h-3.5 w-3.5 rotate-90" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onOutdent(item.id)} disabled={depth === 0} aria-label="Outdent" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5">
                  <Outdent className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onIndent(item.id)} disabled={index === 0} aria-label="Indent" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 disabled:opacity-30 dark:hover:bg-white/5">
                  <Indent className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onAddChild(item.id)} aria-label="Add child item" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5">
                  <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onEdit(item)} aria-label="Edit" className="rounded p-1.5 text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5">
                  <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button type="button" onClick={() => onDelete(item)} aria-label="Delete" className="rounded p-1.5 text-neutral-500 hover:text-danger">
                  <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              </div>
            </div>

            {hasChildren && !isCollapsed && (
              <MenuItemTree
                items={item.children}
                onEdit={onEdit}
                onDelete={onDelete}
                onAddChild={onAddChild}
                onMove={onMove}
                onIndent={onIndent}
                onOutdent={onOutdent}
                onDrop={onDrop}
                depth={depth + 1}
              />
            )}
          </li>
        );
      })}
    </ul>
  );
}
