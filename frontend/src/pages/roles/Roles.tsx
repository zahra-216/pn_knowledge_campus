import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { BASELINE_ROLES } from "@/types/auth";
import { RoleForm } from "./components/RoleForm";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { AdminRole, PermissionsByModule, RolePayload } from "@/types/user";

/**
 * SRS FR-29 — custom role creation beyond the five baseline roles. See
 * RoleForm's docblock and the backend RoleController's docblock for why
 * the five baseline roles can have their permissions edited but never
 * be renamed or deleted.
 */
export function Roles() {
  const { can } = usePermission();
  const { showToast } = useToast();

  const [roles, setRoles] = useState<AdminRole[]>([]);
  const [permissionsByModule, setPermissionsByModule] = useState<PermissionsByModule>({});
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; role: AdminRole | null }>({ open: false, role: null });

  const fetchRoles = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<AdminRole>>(ENDPOINTS.roles.admin());
      setRoles(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("roles.view")) return;
    fetchRoles();
    api.get<ApiResponse<PermissionsByModule>>(ENDPOINTS.roles.permissions).then(({ data }) => setPermissionsByModule(data.data));
  }, [can, fetchRoles]);

  async function handleSave(payload: RolePayload) {
    try {
      if (formState.role) {
        await api.put(ENDPOINTS.roles.admin(formState.role.id), payload);
      } else {
        await api.post(ENDPOINTS.roles.admin(), payload);
      }
      showToast("Role saved.", "success");
      await fetchRoles();
    } catch {
      showToast("Could not save this role.", "error");
    }
  }

  async function handleDelete(role: AdminRole) {
    try {
      await api.delete(ENDPOINTS.roles.admin(role.id));
      showToast("Role deleted.", "success");
      await fetchRoles();
    } catch (err) {
      const message = (err as { response?: { data?: { message?: string } } })?.response?.data?.message;
      showToast(message ?? "Could not delete this role.", "error");
    }
  }

  if (!can("roles.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Users & Roles" }, { label: "Roles" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Role Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<AdminRole>[] = [
    {
      key: "name",
      header: "Role",
      render: (r) => (
        <div className="flex items-center gap-2">
          {r.name}
          {(BASELINE_ROLES as readonly string[]).includes(r.name) && <Badge tone="neutral">Baseline</Badge>}
        </div>
      ),
    },
    { key: "permissions", header: "Permissions", render: (r) => `${r.permissions.length} granted` },
    { key: "users_count", header: "Users", render: (r) => r.users_count },
    {
      key: "actions",
      header: "",
      render: (r) => (
        <div className="flex items-center gap-3">
          <button type="button" onClick={() => setFormState({ open: true, role: r })} className="text-body-sm text-navy hover:underline dark:text-gold">
            Edit
          </button>
          {!(BASELINE_ROLES as readonly string[]).includes(r.name) && (
            <button type="button" onClick={() => handleDelete(r)} className="text-neutral-400 hover:text-danger" aria-label={`Delete ${r.name}`}>
              <Trash2 className="h-4 w-4" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Users & Roles" }, { label: "Roles" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Roles</h1>
        <Button onClick={() => setFormState({ open: true, role: null })}>
          <Plus className="h-4 w-4" aria-hidden="true" />
          New Role
        </Button>
      </div>

      <Card>
        <Table columns={columns} rows={roles} rowKey={(r) => r.id} isLoading={isLoading} emptyTitle="No roles yet" />
      </Card>

      <RoleForm
        open={formState.open}
        role={formState.role}
        isBaseline={!!formState.role && (BASELINE_ROLES as readonly string[]).includes(formState.role.name)}
        permissionsByModule={permissionsByModule}
        onClose={() => setFormState({ open: false, role: null })}
        onSave={handleSave}
      />
    </div>
  );
}
