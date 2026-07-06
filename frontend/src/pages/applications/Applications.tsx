import { useCallback, useEffect, useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { Lock, Search, Download } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { Pagination } from "@/components/public/Pagination";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, PaginationMeta } from "@/types/api";
import type { AdminApplication, ApplicationStatus } from "@/types/application";

const STATUS_TONE: Record<ApplicationStatus, BadgeTone> = {
  draft: "neutral",
  submitted: "info",
  under_review: "warning",
  approved: "success",
  rejected: "danger",
};

const STATUS_LABEL: Record<ApplicationStatus, string> = {
  draft: "Draft",
  submitted: "Submitted",
  under_review: "Under Review",
  approved: "Approved",
  rejected: "Rejected",
};

/**
 * Online Applications review queue (Milestone 20) — SRS-style
 * Permission Matrix, "Applications" row: Super Admin/Administrator/
 * Admissions = Full (view/review/export); Content Editor/Marketing = no
 * access (applicant PII, not editorial content).
 */
export function Applications() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const canExport = can("applications.export");

  const [applications, setApplications] = useState<AdminApplication[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [page, setPage] = useState(1);

  const fetchApplications = useCallback(async (params: { search: string; status: string; page: number }) => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<AdminApplication>>(ENDPOINTS.applications.adminList, {
        params: {
          per_page: 20,
          page: params.page,
          search: params.search || undefined,
          "filter[status]": params.status || undefined,
        },
      });
      setApplications(data.data);
      setMeta(data.meta);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("applications.view")) return;
    fetchApplications({ search, status: statusFilter, page });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [can, statusFilter, page]);

  function handleSearchSubmit(e: FormEvent) {
    e.preventDefault();
    setPage(1);
    fetchApplications({ search, status: statusFilter, page: 1 });
  }

  /**
   * A plain `window.open`/anchor navigation to this URL wouldn't carry
   * the Sanctum bearer token (it's an Authorization header the `api`
   * axios instance attaches, not a cookie) — so the export is fetched
   * through `api` as a blob and saved via a synthetic download link.
   */
  async function handleExport() {
    const { data } = await api.get(ENDPOINTS.applications.adminExport, {
      params: { "filter[status]": statusFilter || undefined },
      responseType: "blob",
    });
    const url = URL.createObjectURL(data as Blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `applications-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  }

  if (!can("applications.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Applications" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Applications.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<AdminApplication>[] = [
    { key: "reference", header: "Reference", render: (a) => <span className="font-mono text-body-sm">{a.application_number}</span> },
    { key: "name", header: "Applicant", render: (a) => `${a.first_name} ${a.last_name}` },
    { key: "email", header: "Email", render: (a) => a.email },
    { key: "course", header: "Course", render: (a) => a.course?.name ?? "—" },
    { key: "status", header: "Status", render: (a) => <Badge tone={STATUS_TONE[a.status]}>{STATUS_LABEL[a.status]}</Badge> },
    {
      key: "submitted_at",
      header: "Submitted",
      render: (a) => (a.submitted_at ? new Date(a.submitted_at).toLocaleDateString() : "—"),
    },
    {
      key: "actions",
      header: "",
      render: (a) => (
        <button type="button" onClick={() => navigate(`/admin/applications/${a.id}`)} className="text-body-sm text-navy hover:underline">
          Review
        </button>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Applications" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Applications</h1>
        {canExport && (
          <Button variant="secondary" onClick={handleExport}>
            <Download className="h-4 w-4" aria-hidden="true" />
            Export CSV
          </Button>
        )}
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <form onSubmit={handleSearchSubmit} className="relative flex-1 min-w-[220px]">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search name, email, or reference..."
            className="h-10 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
          />
        </form>

        <select
          value={statusFilter}
          onChange={(e) => {
            setStatusFilter(e.target.value);
            setPage(1);
          }}
          className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body-sm"
        >
          <option value="">All Statuses</option>
          <option value="submitted">Submitted</option>
          <option value="under_review">Under Review</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <Card>
        <Table
          columns={columns}
          rows={applications}
          rowKey={(a) => a.id}
          isLoading={isLoading}
          emptyTitle="No applications yet"
          emptyDescription="Submitted applications will appear here for review."
        />
      </Card>

      {meta && <Pagination meta={meta} onPageChange={setPage} />}
    </div>
  );
}
