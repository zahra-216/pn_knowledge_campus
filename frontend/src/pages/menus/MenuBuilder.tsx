import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Plus, Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Spinner, EmptyState, useToast } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { MenuItemTree } from "./components/MenuItemTree";
import { MenuItemForm } from "./components/MenuItemForm";
import { buildTree, flatten, indentItem, moveItem, outdentItem, swapItems, type FlatMenuItem } from "./utils/menuTree";
import type { ApiResponse } from "@/types/api";
import type { MenuItem, MenuItemPayload, MenuSummary } from "@/types/menu";

/**
 * Development Roadmap (Menu Builder hardening) — the nested drag-and-drop
 * tree editor for a single menu. UI/UX Design, Section 6.4.
 */
export function MenuBuilder() {
  const { id } = useParams<{ id: string }>();
  const menuId = Number(id);
  const { can } = usePermission();
  const { showToast } = useToast();
  const canEdit = can("menus.edit");

  const [menu, setMenu] = useState<MenuSummary | null>(null);
  const [flatItems, setFlatItems] = useState<FlatMenuItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; item: MenuItem | null; parentId: number | null }>({
    open: false,
    item: null,
    parentId: null,
  });

  const fetchAll = useCallback(async () => {
    setIsLoading(true);
    try {
      const [menusRes, itemsRes] = await Promise.all([
        api.get<ApiResponse<MenuSummary[]>>(ENDPOINTS.menus.admin()),
        api.get<ApiResponse<MenuItem[]>>(ENDPOINTS.menus.items(menuId)),
      ]);
      setMenu(menusRes.data.data.find((m) => m.id === menuId) ?? null);
      setFlatItems(flatten(itemsRes.data.data));
    } finally {
      setIsLoading(false);
    }
  }, [menuId]);

  useEffect(() => {
    if (!can("menus.view")) return;
    fetchAll();
  }, [fetchAll, can]);

  async function persist(next: FlatMenuItem[]) {
    setFlatItems(next);
    try {
      await api.patch(ENDPOINTS.menus.reorder(menuId), {
        items: next.map((i) => ({ id: i.id, parent_id: i.parent_id, order: i.order })),
      });
    } catch {
      showToast("Could not save the new order.", "error");
      fetchAll();
    }
  }

  async function handleSaveItem(payload: MenuItemPayload) {
    try {
      if (formState.item) {
        await api.put(ENDPOINTS.menus.items(menuId, formState.item.id), payload);
      } else {
        await api.post(ENDPOINTS.menus.items(menuId), { ...payload, parent_id: formState.parentId });
      }
      showToast("Menu item saved.", "success");
      await fetchAll();
    } catch {
      showToast("Could not save this item. Check the link and required fields.", "error");
    }
  }

  async function handleDelete(item: MenuItem) {
    await api.delete(ENDPOINTS.menus.items(menuId, item.id));
    await fetchAll();
  }

  if (!can("menus.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Menus", to: "/admin/menus" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            Only Super Admins and Administrators can access the Menu Builder.
          </div>
        </Card>
      </div>
    );
  }

  const tree = buildTree(flatItems);

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Menus", to: "/admin/menus" }, { label: menu?.name ?? "..." }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold capitalize text-[color:var(--color-text)]">{menu?.name ?? "Menu"}</h1>
        {canEdit && (
          <Button onClick={() => setFormState({ open: true, item: null, parentId: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            Add Item
          </Button>
        )}
      </div>

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-16">
            <Spinner />
          </div>
        ) : tree.length === 0 ? (
          <EmptyState title="No items yet" description="Add your first navigation link to get started." />
        ) : (
          <MenuItemTree
            items={tree}
            onEdit={(item) => setFormState({ open: true, item, parentId: null })}
            onDelete={handleDelete}
            onAddChild={(parentId) => setFormState({ open: true, item: null, parentId })}
            onMove={(itemId, direction) => persist(moveItem(flatItems, itemId, direction))}
            onIndent={(itemId) => persist(indentItem(flatItems, itemId))}
            onOutdent={(itemId) => persist(outdentItem(flatItems, itemId))}
            onDrop={(draggedId, targetId) => persist(swapItems(flatItems, draggedId, targetId))}
          />
        )}
      </Card>

      <MenuItemForm
        open={formState.open}
        item={formState.item}
        onClose={() => setFormState({ open: false, item: null, parentId: null })}
        onSave={handleSaveItem}
      />
    </div>
  );
}
