import type { MenuItem } from "@/types/menu";

/** A flat (non-nested) item — same shape as MenuItem but children is always []. */
export type FlatMenuItem = Omit<MenuItem, "children">;

/** Flattens the API's nested tree into a flat list for easy reordering. */
export function flatten(items: MenuItem[]): FlatMenuItem[] {
  const result: FlatMenuItem[] = [];

  function walk(nodes: MenuItem[]) {
    for (const node of nodes) {
      const { children, ...rest } = node;
      result.push(rest);
      walk(children);
    }
  }

  walk(items);
  return result;
}

/** Rebuilds a nested tree from a flat list, sorted by `order` at every level. */
export function buildTree(items: FlatMenuItem[]): MenuItem[] {
  const byParent = new Map<number | null, FlatMenuItem[]>();

  for (const item of items) {
    const key = item.parent_id;
    if (!byParent.has(key)) byParent.set(key, []);
    byParent.get(key)!.push(item);
  }

  function attach(parentId: number | null): MenuItem[] {
    const siblings = (byParent.get(parentId) ?? []).slice().sort((a, b) => a.order - b.order);
    return siblings.map((item) => ({ ...item, children: attach(item.id) }));
  }

  return attach(null);
}

function siblingsOf(items: FlatMenuItem[], parentId: number | null): FlatMenuItem[] {
  return items.filter((i) => i.parent_id === parentId).sort((a, b) => a.order - b.order);
}

/** Reassigns 0..n-1 to every sibling under `parentId`, preserving their relative order. */
function renumber(items: FlatMenuItem[], parentId: number | null): FlatMenuItem[] {
  const siblings = siblingsOf(items, parentId);
  const orderById = new Map(siblings.map((item, index) => [item.id, index]));

  return items.map((item) => (orderById.has(item.id) ? { ...item, order: orderById.get(item.id)! } : item));
}

/** Swaps an item with its previous/next sibling (same parent_id). */
export function moveItem(items: FlatMenuItem[], id: number, direction: "up" | "down"): FlatMenuItem[] {
  const item = items.find((i) => i.id === id);
  if (!item) return items;

  const siblings = siblingsOf(items, item.parent_id);
  const index = siblings.findIndex((i) => i.id === id);
  const swapWith = direction === "up" ? siblings[index - 1] : siblings[index + 1];
  if (!swapWith) return items;

  return items.map((i) => {
    if (i.id === item.id) return { ...i, order: swapWith.order };
    if (i.id === swapWith.id) return { ...i, order: item.order };
    return i;
  });
}

/**
 * Drag-and-drop reorder: swaps two items' positions. Only meaningful
 * (and only called) when both share the same parent — dragging across
 * levels isn't supported here, that's what Indent/Outdent are for.
 */
export function swapItems(items: FlatMenuItem[], draggedId: number, targetId: number): FlatMenuItem[] {
  if (draggedId === targetId) return items;

  const dragged = items.find((i) => i.id === draggedId);
  const target = items.find((i) => i.id === targetId);
  if (!dragged || !target || dragged.parent_id !== target.parent_id) return items;

  return items.map((i) => {
    if (i.id === draggedId) return { ...i, order: target.order };
    if (i.id === targetId) return { ...i, order: dragged.order };
    return i;
  });
}

/** Makes an item a child of its previous sibling (becomes that sibling's last child). */
export function indentItem(items: FlatMenuItem[], id: number): FlatMenuItem[] {
  const item = items.find((i) => i.id === id);
  if (!item) return items;

  const siblings = siblingsOf(items, item.parent_id);
  const index = siblings.findIndex((i) => i.id === id);
  const newParent = siblings[index - 1];
  if (!newParent) return items; // first item in its level has nothing to indent under

  const oldParentId = item.parent_id;
  const moved = items.map((i) => (i.id === id ? { ...i, parent_id: newParent.id } : i));

  return renumber(renumber(moved, newParent.id), oldParentId);
}

/** Moves an item up one level, placed immediately after its former parent. */
export function outdentItem(items: FlatMenuItem[], id: number): FlatMenuItem[] {
  const item = items.find((i) => i.id === id);
  if (!item || item.parent_id === null) return items;

  const oldParent = items.find((i) => i.id === item.parent_id);
  if (!oldParent) return items;

  const oldParentId = item.parent_id;
  const newParentId = oldParent.parent_id;

  // Place it right after the old parent among the new siblings, before renumbering.
  const moved = items.map((i) => (i.id === id ? { ...i, parent_id: newParentId, order: oldParent.order + 0.5 } : i));

  return renumber(renumber(moved, newParentId), oldParentId);
}
