import { useCallback, useEffect, useState, type FormEvent } from "react";
import { Lock, Search, Download, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Table, Badge, Modal, Button, useToast, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { Pagination } from "@/components/public/Pagination";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, PaginationMeta } from "@/types/api";
import type { AdminInquiry, InquiryStatus } from "@/types/inquiry";

const STATUS_TONE: Record<InquiryStatus, BadgeTone> = {
  new: "info",
  in_progress: "warning",
  resolved: "success",
  spam: "danger",
};

const STATUS_LABEL: Record<InquiryStatus, string> = {
  new: "New",
  in_progress: "In Progress",
  resolved: "Resolved",
  spam: "Spam",
};

const STATUS_OPTIONS: InquiryStatus[] = ["new", "in_progress", "resolved", "spam"];

/**
 * Inquiry Management admin inbox (SRS FR-05/FR-26) — SRS Permission
 * Matrix, "Inquiry Management" row: Super Admin/Administrator/
 * Admissions = Full; Marketing = View; Content Editor = no access. Logs
 * every Contact form / Course Detail "Enquire Now" submission with a
 * flat status workflow (New/In Progress/Resolved/Spam) — no assignment
 * field exists on the `inquiries` table today, so unlike Applications
 * there's no "assign to staff member" action here.
 */
export function Inquiries() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canManage = can("inquiries.manage");
  const canExport = can("inquiries.export");

  const [inquiries, setInquiries] = useState<AdminInquiry[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [page, setPage] = useState(1);
  const [active, setActive] = useState<AdminInquiry | null>(null);

  const fetchInquiries = useCallback(async (params: { search: string; status: string; page: number }) => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<AdminInquiry>>(ENDPOINTS.inquiries.adminList, {
        params: {
          per_page: 20,
          page: params.page,
          search: params.search || undefined,
          "filter[status]": params.status || undefined,
        },
      });
      setInquiries(data.data);
      setMeta(data.meta);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("inquiries.view")) return;
    fetchInquiries({ search, status: statusFilter, page });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [can, statusFilter, page]);

  function handleSearchSubmit(e: FormEvent) {
    e.preventDefault();
    setPage(1);
    fetchInquiries({ search, status: statusFilter, page: 1 });
  }

  async function handleStatusChange(inquiry: AdminInquiry, status: InquiryStatus) {
    try {
      await api.patch(ENDPOINTS.inquiries.adminStatus(inquiry.id), { status });
      showToast("Status updated.", "success");
      setActive((current) => (current ? { ...current, status } : current));
      await fetchInquiries({ search, status: statusFilter, page });
    } catch {
      showToast("Could not update status.", "error");
    }
  }

  async function handleDelete(inquiry: AdminInquiry) {
    try {
      await api.delete(ENDPOINTS.inquiries.adminShow(inquiry.id));
      showToast("Inquiry deleted.", "success");
      setActive(null);
      await fetchInquiries({ search, status: statusFilter, page });
    } catch {
      showToast("Could not delete this inquiry.", "error");
    }
  }

  async function handleExport() {
    const { data } = await api.get(ENDPOINTS.inquiries.adminExport, {
      params: { "filter[status]": statusFilter || undefined },
      responseType: "blob",
    });
    const url = URL.createObjectURL(data as Blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `inquiries-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  }

  if (!can("inquiries.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Inquiries" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Inquiries.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<AdminInquiry>[] = [
    { key: "name", header: "Name", render: (i) => i.name },
    { key: "email", header: "Email", render: (i) => i.email },
    { key: "source", header: "Source", render: (i) => i.source_page ?? "—" },
    { key: "course", header: "Course", render: (i) => i.course?.name ?? "—" },
    { key: "status", header: "Status", render: (i) => <Badge tone={STATUS_TONE[i.status]}>{STATUS_LABEL[i.status]}</Badge> },
    { key: "created_at", header: "Submitted", render: (i) => new Date(i.created_at).toLocaleDateString() },
    {
      key: "actions",
      header: "",
      render: (i) => (
        <button type="button" onClick={() => setActive(i)} className="text-body-sm text-navy hover:underline dark:text-gold">
          View
        </button>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Inquiries" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Inquiries</h1>
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
            placeholder="Search name, email, or message..."
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
          {STATUS_OPTIONS.map((status) => (
            <option key={status} value={status}>
              {STATUS_LABEL[status]}
            </option>
          ))}
        </select>
      </div>

      <Card>
        <Table
          columns={columns}
          rows={inquiries}
          rowKey={(i) => i.id}
          isLoading={isLoading}
          emptyTitle="No inquiries yet"
          emptyDescription="Contact form and course enquiry submissions will appear here."
        />
      </Card>

      {meta && <Pagination meta={meta} onPageChange={setPage} />}

      <Modal open={!!active} onClose={() => setActive(null)} title={active?.name ?? "Inquiry"}>
        {active && (
          <div className="flex flex-col gap-4">
            <dl className="grid grid-cols-[100px_1fr] gap-x-3 gap-y-2 text-body-sm">
              <dt className="text-neutral-500">Email</dt>
              <dd className="text-[color:var(--color-text)]">{active.email}</dd>
              <dt className="text-neutral-500">Phone</dt>
              <dd className="text-[color:var(--color-text)]">{active.phone ?? "—"}</dd>
              <dt className="text-neutral-500">Source</dt>
              <dd className="text-[color:var(--color-text)]">{active.source_page ?? "—"}</dd>
              {active.course && (
                <>
                  <dt className="text-neutral-500">Course</dt>
                  <dd className="text-[color:var(--color-text)]">{active.course.name}</dd>
                </>
              )}
              {active.international_applicant && (
                <>
                  <dt className="text-neutral-500">Applicant</dt>
                  <dd className="text-[color:var(--color-text)]">International</dd>
                </>
              )}
              <dt className="text-neutral-500">Submitted</dt>
              <dd className="text-[color:var(--color-text)]">{new Date(active.created_at).toLocaleString()}</dd>
            </dl>

            <div>
              <p className="text-caption font-semibold uppercase tracking-wide text-neutral-500">Message</p>
              <p className="mt-1 whitespace-pre-line text-body text-[color:var(--color-text)]">{active.message}</p>
            </div>

            {canManage && (
              <div className="flex flex-wrap items-center justify-between gap-3 border-t border-[color:var(--color-border)] pt-4">
                <select
                  value={active.status}
                  onChange={(e) => handleStatusChange(active, e.target.value as InquiryStatus)}
                  className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body-sm"
                >
                  {STATUS_OPTIONS.map((status) => (
                    <option key={status} value={status}>
                      {STATUS_LABEL[status]}
                    </option>
                  ))}
                </select>

                <Button variant="danger" onClick={() => handleDelete(active)}>
                  <Trash2 className="h-4 w-4" aria-hidden="true" />
                  Delete
                </Button>
              </div>
            )}
          </div>
        )}
      </Modal>
    </div>
  );
}
