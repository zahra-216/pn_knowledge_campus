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
import type { Department } from "@/types/department";

/**
 * Department Management — SRS Permission Matrix, "Department Management"
 * row: Super Admin/Administrator = Full; Content Editor = Create/Edit;
 * Marketing/Admissions = View. Identical split to Faculty Management.
 */
export function Departments() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("departments.create");
  const canDelete = can("departments.delete");

  const [departments, setDepartments] = useState<Department[]>([]);
  const [faculties, setFaculties] = useState<Faculty[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchDepartments = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Department>>(ENDPOINTS.departments.admin(), { params: { per_page: 100 } });
      setDepartments(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("departments.view")) return;
    fetchDepartments();
    api.get<ApiCollection<Faculty>>(ENDPOINTS.faculties.admin(), { params: { per_page: 100 } }).then(({ data }) => setFaculties(data.data));
  }, [fetchDepartments, can]);

  async function handleCreate() {
    if (faculties.length === 0) {
      showToast("Create a faculty first — a department must belong to one.", "error");
      return;
    }

    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<Department>>(ENDPOINTS.departments.admin(), {
        name: "New Department",
        faculty_id: faculties[0].id,
      });
      navigate(`/admin/departments/${data.data.id}`);
    } catch {
      showToast("Could not create a new department.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(department: Department) {
    try {
      await api.delete(ENDPOINTS.departments.admin(department.id));
      await fetchDepartments();
    } catch (err) {
      showToast((err as ApiError).message, "error");
    }
  }

  if (!can("departments.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Departments" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Department Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Department>[] = [
    {
      key: "banner",
      header: "",
      render: (d) =>
        d.banner_url ? (
          <img src={d.banner_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "name", header: "Name", render: (d) => d.name },
    { key: "faculty", header: "Faculty", render: (d) => d.faculty?.name ?? "—" },
    { key: "order", header: "Order", render: (d) => d.order },
    { key: "status", header: "Status", render: (d) => <Badge tone={d.status === "published" ? "success" : "neutral"}>{d.status}</Badge> },
    {
      key: "actions",
      header: "",
      render: (d) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/departments/${d.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(d)} aria-label={`Delete ${d.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Departments" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Departments</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Department
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={departments}
          rowKey={(d) => d.id}
          isLoading={isLoading}
          emptyTitle="No departments yet"
          emptyDescription="Add your first department under a faculty."
        />
      </Card>
    </div>
  );
}
