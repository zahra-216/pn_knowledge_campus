import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiError, ApiResponse } from "@/types/api";
import type { CourseCategory } from "@/types/course";

/** Flattens the category tree into depth-first, indented rows (Category > Subcategory). */
function flattenCategories(categories: CourseCategory[], parentId: number | null = null, depth = 0): (CourseCategory & { depth: number })[] {
  return categories
    .filter((c) => c.parent_id === parentId)
    .sort((a, b) => a.order - b.order)
    .flatMap((c) => [{ ...c, depth }, ...flattenCategories(categories, c.id, depth + 1)]);
}

/**
 * Course Category Management — Course Management sub-part (gated by
 * courses.* per the SRS Permission Matrix, same as CourseLevel/
 * CourseMode). Unlike those two flat lookups, Category has its own icon/
 * image/SEO and a parent/child tree, so it gets a full editor page
 * instead of a modal (see CourseCategoryEditor).
 */
export function CourseCategories() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("courses.create");
  const canDelete = can("courses.delete");

  const [categories, setCategories] = useState<CourseCategory[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchCategories = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<CourseCategory[]>>(ENDPOINTS.courseCategories.admin());
      setCategories(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("courses.view")) return;
    fetchCategories();
  }, [fetchCategories, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<CourseCategory>>(ENDPOINTS.courseCategories.admin(), { name: "New Category" });
      navigate(`/admin/course-categories/${data.data.id}`);
    } catch {
      showToast("Could not create a new category.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(category: CourseCategory) {
    try {
      await api.delete(ENDPOINTS.courseCategories.admin(category.id));
      await fetchCategories();
    } catch (err) {
      showToast((err as ApiError).message, "error");
    }
  }

  if (!can("courses.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Course Categories" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Course Management.
          </div>
        </Card>
      </div>
    );
  }

  const rows = flattenCategories(categories);

  const columns: TableColumn<CourseCategory & { depth: number }>[] = [
    {
      key: "icon",
      header: "",
      render: (c) =>
        c.icon_url ? (
          <img src={c.icon_url} alt="" className="h-8 w-8 rounded-sm object-cover" />
        ) : (
          <div className="h-8 w-8 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    {
      key: "name",
      header: "Name",
      render: (c) => (
        <span style={{ paddingLeft: `${c.depth * 1.25}rem` }} className="inline-flex items-center gap-1.5">
          {c.depth > 0 && <span className="text-neutral-400">—</span>}
          {c.name}
        </span>
      ),
    },
    { key: "slug", header: "Slug", render: (c) => <span className="text-neutral-500">{c.slug}</span> },
    { key: "courses_count", header: "Courses", render: (c) => c.courses_count ?? 0 },
    { key: "order", header: "Order", render: (c) => c.order },
    {
      key: "actions",
      header: "",
      render: (c) => (
        <div className="flex gap-3">
          <button
            type="button"
            onClick={() => navigate(`/admin/course-categories/${c.id}`)}
            className="text-body-sm text-navy hover:underline"
          >
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(c)} aria-label={`Delete ${c.name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Course Categories" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Course Categories</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Category
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={rows}
          rowKey={(c) => c.id}
          isLoading={isLoading}
          emptyTitle="No categories yet"
          emptyDescription="Add categories like Business & Management, then subcategories underneath."
        />
      </Card>
    </div>
  );
}
