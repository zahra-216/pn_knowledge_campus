import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiError, ApiResponse } from "@/types/api";
import type { Faculty } from "@/types/faculty";

/**
 * Faculty Management — SRS Permission Matrix, "Faculty Management" row:
 * Super Admin/Administrator = Full; Content Editor = Create/Edit;
 * Marketing/Admissions = View.
 */
export function Faculties() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("faculties.create");
  const canDelete = can("faculties.delete");

  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchFaculties = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Faculty>>(ENDPOINTS.faculties.admin(), { params: { per_page: 100 } });
      setFaculties(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("faculties.view")) return;
    fetchFaculties();
  }, [fetchFaculties, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<Faculty>>(ENDPOINTS.faculties.admin(), { name: "New Faculty" });
      navigate(`/admin/faculties/${data.data.id}`);
    } catch {
      showToast("Could not create a new faculty.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(faculty: Faculty) {
    try {
      await api.delete(ENDPOINTS.faculties.admin(faculty.id));
      await fetchFaculties();
    } catch (err) {
      // 409 when the faculty still has departments assigned to it
      // (FacultyController::destroy()'s dependent-records check).
      showToast((err as ApiError).message, "error");
    }
  }

  if (!can("faculties.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Faculties" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Faculty Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Faculty>[] = [
    {
      key: "icon",
      header: "",
      render: (f) =>
        f.icon_url ? (
          <img src={f.icon_url} alt="" className="h-10 w-10 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-10 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "name", header: "Name", render: (f) => f.name },
    { key: "slug", header: "Slug", render: (f) => <span className="text-neutral-500">/{f.slug}</span> },
    { key: "order", header: "Order", render: (f) => f.order },
    { key: "status", header: "Status", render: (f) => <Badge tone={f.status === "published" ? "success" : "neutral"}>{f.status}</Badge> },
    {
      key: "actions",
      header: "",
      render: (f) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/faculties/${f.id}`)} className="text-body-sm text-gold hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(f)} aria-label={`Delete ${f.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Faculties" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Faculties</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Faculty
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={faculties}
          rowKey={(f) => f.id}
          isLoading={isLoading}
          emptyTitle="No faculties yet"
          emptyDescription="Add your first faculty (e.g. Faculty of Business)."
        />
      </Card>
    </div>
  );
}
