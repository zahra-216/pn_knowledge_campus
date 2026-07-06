import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Modal, Input, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiResponse } from "@/types/api";
import type { MenuSummary } from "@/types/menu";

/**
 * Menu Builder — list of menus (header, footer, and any custom menus an
 * admin creates). UI/UX Design, Admin Sitemap: Content > Menus.
 */
export function Menus() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canEdit = can("menus.edit");

  const [menus, setMenus] = useState<MenuSummary[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreateOpen, setIsCreateOpen] = useState(false);
  const [newName, setNewName] = useState("");

  const fetchMenus = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<MenuSummary[]>>(ENDPOINTS.menus.admin());
      setMenus(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("menus.view")) return;
    fetchMenus();
  }, [fetchMenus, can]);

  async function handleCreate() {
    if (!newName.trim()) return;
    try {
      await api.post(ENDPOINTS.menus.admin(), { name: newName.trim() });
      setNewName("");
      setIsCreateOpen(false);
      await fetchMenus();
    } catch {
      showToast("Could not create this menu. The name may already be in use.", "error");
    }
  }

  async function handleDelete(menu: MenuSummary) {
    await api.delete(ENDPOINTS.menus.admin(menu.id));
    await fetchMenus();
  }

  if (!can("menus.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Menus" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            Only Super Admins and Administrators can access the Menu Builder.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<MenuSummary>[] = [
    { key: "name", header: "Name", render: (m) => <span className="capitalize">{m.name}</span> },
    { key: "items_count", header: "Items", render: (m) => m.items_count },
    {
      key: "actions",
      header: "",
      render: (m) => (
        <div className="flex gap-2">
          <button type="button" onClick={() => navigate(`/admin/menus/${m.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canEdit && (
            <button type="button" onClick={() => handleDelete(m)} aria-label={`Delete ${m.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Menus" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Menus</h1>
        {canEdit && (
          <Button onClick={() => setIsCreateOpen(true)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Menu
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={menus}
          rowKey={(m) => m.id}
          isLoading={isLoading}
          emptyTitle="No menus yet"
          emptyDescription="Create your first menu (e.g. header, footer)."
        />
      </Card>

      <Modal
        open={isCreateOpen}
        onClose={() => setIsCreateOpen(false)}
        title="New Menu"
        footer={
          <>
            <Button variant="secondary" onClick={() => setIsCreateOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleCreate}>Create</Button>
          </>
        }
      >
        <Input
          label="Name"
          placeholder="e.g. header, footer, utility"
          value={newName}
          onChange={(e) => setNewName(e.target.value)}
        />
      </Modal>
    </div>
  );
}
