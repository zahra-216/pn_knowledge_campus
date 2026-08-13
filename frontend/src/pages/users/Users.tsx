import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2, Search } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn, Pagination } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { useAuth } from "@/context/AuthContext";
import { UserForm } from "./components/UserForm";
import type { ApiCollection, ApiResponse, PaginationMeta } from "@/types/api";
import type { AdminUser, UserCreatePayload, UserUpdatePayload } from "@/types/user";
import type { AdminRole } from "@/types/user";

/**
 * Users, Roles & Permissions module (SRS FR-29) — Super Admin only, per
 * the Permission Matrix's "Users, Roles & Permissions" row (stricter
 * than every other module, which is at least readable by Administrator).
 */
export function Users() {
  const { can } = usePermission();
  const { user: currentUser } = useAuth();
  const { showToast } = useToast();
  const canCreate = can("users.create");
  const canEdit = can("users.edit");
  const canDelete = can("users.delete");

  const [users, setUsers] = useState<AdminUser[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [roles, setRoles] = useState<AdminRole[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [formState, setFormState] = useState<{ open: boolean; user: AdminUser | null }>({ open: false, user: null });

  const fetchUsers = useCallback(async (params: { search: string; page: number }) => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<AdminUser>>(ENDPOINTS.users.admin(), {
        params: { per_page: 20, page: params.page, search: params.search || undefined },
      });
      setUsers(data.data);
      setMeta(data.meta);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("users.view")) return;
    fetchUsers({ search, page });
    api.get<ApiResponse<AdminRole[]>>(ENDPOINTS.roles.admin()).then(({ data }) => setRoles(data.data));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [can, page]);

  async function handleSave(payload: UserCreatePayload | UserUpdatePayload) {
    try {
      if (formState.user) {
        await api.put(ENDPOINTS.users.admin(formState.user.id), payload);
      } else {
        await api.post(ENDPOINTS.users.admin(), payload);
      }
      showToast("User saved.", "success");
      await fetchUsers({ search, page });
    } catch {
      showToast("Could not save this user.", "error");
    }
  }

  async function handleDelete(user: AdminUser) {
    try {
      await api.delete(ENDPOINTS.users.admin(user.id));
      showToast("User deleted.", "success");
      await fetchUsers({ search, page });
    } catch (err) {
      const message = (err as { response?: { data?: { message?: string } } })?.response?.data?.message;
      showToast(message ?? "Could not delete this user.", "error");
    }
  }

  if (!can("users.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Users & Roles" }, { label: "Users" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to User Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<AdminUser>[] = [
    { key: "name", header: "Name", render: (u) => u.name },
    { key: "email", header: "Email", render: (u) => u.email },
    { key: "role", header: "Role", render: (u) => u.role ?? "—" },
    {
      key: "status",
      header: "Status",
      render: (u) => <Badge tone={u.is_active ? "success" : "neutral"}>{u.is_active ? "Active" : "Inactive"}</Badge>,
    },
    {
      key: "last_login_at",
      header: "Last Login",
      render: (u) => (u.last_login_at ? new Date(u.last_login_at).toLocaleString() : "Never"),
    },
    {
      key: "actions",
      header: "",
      render: (u) => (
        <div className="flex items-center gap-3">
          {canEdit && (
            <button type="button" onClick={() => setFormState({ open: true, user: u })} className="text-body-sm text-navy hover:underline dark:text-gold">
              Edit
            </button>
          )}
          {canDelete && u.id !== currentUser?.id && (
            <button type="button" onClick={() => handleDelete(u)} className="text-neutral-400 hover:text-danger" aria-label={`Delete ${u.name}`}>
              <Trash2 className="h-4 w-4" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Users & Roles" }, { label: "Users" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Users</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, user: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New User
          </Button>
        )}
      </div>

      <div className="relative max-w-sm">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === "Enter") {
              setPage(1);
              fetchUsers({ search, page: 1 });
            }
          }}
          placeholder="Search name or email..."
          className="h-10 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
        />
      </div>

      <Card>
        <Table columns={columns} rows={users} rowKey={(u) => u.id} isLoading={isLoading} emptyTitle="No users yet" />
      </Card>

      {meta && <Pagination meta={meta} onPageChange={setPage} />}

      <UserForm
        open={formState.open}
        user={formState.user}
        roleNames={roles.map((r) => r.name)}
        isSelf={formState.user?.id === currentUser?.id}
        onClose={() => setFormState({ open: false, user: null })}
        onSave={handleSave}
      />
    </div>
  );
}
