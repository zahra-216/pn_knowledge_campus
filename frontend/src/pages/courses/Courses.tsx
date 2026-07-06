import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import { CourseCreateModal } from "./components/CourseCreateModal";
import type { ApiCollection } from "@/types/api";
import type { Course, CourseStatus } from "@/types/course";

const STATUS_TONE: Record<CourseStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * Course Management — SRS Permission Matrix, "Course Management" row:
 * Super Admin/Administrator = Full; Content Editor = Create/Edit;
 * Marketing = View; Admissions = Create/Edit.
 */
export function Courses() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const canCreate = can("courses.create");
  const canDelete = can("courses.delete");

  const [courses, setCourses] = useState<Course[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreateOpen, setIsCreateOpen] = useState(false);

  const fetchCourses = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Course>>(ENDPOINTS.courses.admin(), { params: { per_page: 100 } });
      setCourses(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("courses.view")) return;
    fetchCourses();
  }, [fetchCourses, can]);

  async function handleDelete(course: Course) {
    await api.delete(ENDPOINTS.courses.admin(course.id));
    await fetchCourses();
  }

  if (!can("courses.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Courses" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Course Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Course>[] = [
    {
      key: "image",
      header: "",
      render: (c) =>
        c.featured_image_url ? (
          <img src={c.featured_image_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "course_name", header: "Course", render: (c) => c.course_name },
    { key: "course_code", header: "Code", render: (c) => c.course_code },
    { key: "faculty", header: "Faculty", render: (c) => c.faculty?.name ?? "—" },
    { key: "department", header: "Department", render: (c) => c.department?.name ?? "—" },
    { key: "level", header: "Level", render: (c) => c.level?.name ?? "—" },
    {
      key: "status",
      header: "Status",
      render: (c) => (
        <div className="flex gap-1.5">
          {c.is_featured && <Badge tone="info">Featured</Badge>}
          <Badge tone={STATUS_TONE[c.status]}>{c.status}</Badge>
        </div>
      ),
    },
    {
      key: "actions",
      header: "",
      render: (c) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/courses/${c.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(c)} aria-label={`Delete ${c.course_name}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Courses" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Courses</h1>
        {canCreate && (
          <Button onClick={() => setIsCreateOpen(true)}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Course
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={courses}
          rowKey={(c) => c.id}
          isLoading={isLoading}
          emptyTitle="No courses yet"
          emptyDescription="Add your first course under a Faculty and Department."
        />
      </Card>

      <CourseCreateModal
        open={isCreateOpen}
        onClose={() => setIsCreateOpen(false)}
        onCreated={(course) => {
          setIsCreateOpen(false);
          navigate(`/admin/courses/${course.id}`);
        }}
      />
    </div>
  );
}
