import { useCallback, useEffect, useState } from "react";
import { ArrowLeft, Lock, Search as SearchIcon, CheckCircle2, AlertTriangle } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Table, Badge, Modal, type TableColumn } from "@/components/ui";
import { Pagination } from "@/components/public/Pagination";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse, PaginationMeta } from "@/types/api";
import type { SeoableType, SeoEntitySummary, SeoTypeSummary } from "@/types/seo";

/**
 * SEO Manager overview (SRS Permission Matrix, "SEO Manager" row: Super
 * Admin/Administrator/Content Editor/Marketing = Full or Create/Edit;
 * Admissions = no access). This screen doesn't re-implement the SEO
 * field editor — it's an index across every seoable entity type
 * (SeoMetaController::index/typeIndex) that opens the exact same
 * SeoFieldsPanel every entity's own editor already uses (in a Modal
 * here, instead of embedded in that entity's own page), so there's one
 * real editor implementation, not two.
 */
export function SeoManager() {
  const { can } = usePermission();
  const canEdit = can("seo.edit");

  const [summary, setSummary] = useState<SeoTypeSummary[]>([]);
  const [isLoadingSummary, setIsLoadingSummary] = useState(true);
  const [selectedType, setSelectedType] = useState<SeoTypeSummary | null>(null);

  const [entities, setEntities] = useState<SeoEntitySummary[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [isLoadingEntities, setIsLoadingEntities] = useState(false);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [editing, setEditing] = useState<SeoEntitySummary | null>(null);

  const fetchSummary = useCallback(async () => {
    setIsLoadingSummary(true);
    try {
      const { data } = await api.get<ApiResponse<SeoTypeSummary[]>>(ENDPOINTS.seo.summary);
      setSummary(data.data);
    } finally {
      setIsLoadingSummary(false);
    }
  }, []);

  const fetchEntities = useCallback(async (type: SeoableType, params: { search: string; page: number }) => {
    setIsLoadingEntities(true);
    try {
      const { data } = await api.get<ApiCollection<SeoEntitySummary>>(ENDPOINTS.seo.typeList(type), {
        params: { per_page: 20, page: params.page, search: params.search || undefined },
      });
      setEntities(data.data);
      setMeta(data.meta);
    } finally {
      setIsLoadingEntities(false);
    }
  }, []);

  useEffect(() => {
    if (!can("seo.view")) return;
    fetchSummary();
  }, [can, fetchSummary]);

  useEffect(() => {
    if (!selectedType) return;
    fetchEntities(selectedType.type, { search, page });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedType, page]);

  function openType(type: SeoTypeSummary) {
    setSelectedType(type);
    setSearch("");
    setPage(1);
  }

  async function handleModalClose() {
    setEditing(null);
    if (selectedType) await fetchEntities(selectedType.type, { search, page });
    await fetchSummary();
  }

  if (!can("seo.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "SEO Manager" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to the SEO Manager.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<SeoEntitySummary>[] = [
    { key: "label", header: "Title", render: (e) => e.label },
    {
      key: "status",
      header: "SEO Status",
      render: (e) =>
        e.has_seo ? (
          <Badge tone="success">
            <CheckCircle2 className="mr-1 inline h-3.5 w-3.5" /> Configured
          </Badge>
        ) : (
          <Badge tone="warning">
            <AlertTriangle className="mr-1 inline h-3.5 w-3.5" /> Missing
          </Badge>
        ),
    },
    { key: "seo_title", header: "SEO Title", render: (e) => e.seo_title ?? "—" },
    { key: "robots", header: "Indexable", render: (e) => (e.robots_index ? "Yes" : "No") },
    {
      key: "actions",
      header: "",
      render: (e) => (
        <button type="button" onClick={() => setEditing(e)} className="text-body-sm text-navy hover:underline dark:text-gold">
          {canEdit ? "Edit SEO" : "View SEO"}
        </button>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={selectedType ? [{ label: "SEO Manager" }, { label: selectedType.label }] : [{ label: "SEO Manager" }]} />

      {!selectedType ? (
        <>
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">SEO Manager</h1>
          <p className="text-body-sm text-neutral-500">
            An overview of SEO coverage across every content type. Select a type below to review or edit individual entries.
          </p>

          {isLoadingSummary ? (
            <Card>Loading...</Card>
          ) : (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              {summary.map((row) => (
                <button key={row.type} type="button" onClick={() => openType(row)} className="text-left">
                  <Card className="flex flex-col gap-2 transition-shadow hover:shadow-2">
                    <p className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{row.label}</p>
                    <p className="text-h2 font-display font-semibold text-navy dark:text-white">{row.total}</p>
                    <p className="text-body-sm text-neutral-500">
                      {row.with_seo} configured
                      {row.missing > 0 && <span className="text-warning"> · {row.missing} missing</span>}
                    </p>
                  </Card>
                </button>
              ))}
            </div>
          )}
        </>
      ) : (
        <>
          <div className="flex items-center justify-between">
            <button
              type="button"
              onClick={() => setSelectedType(null)}
              className="flex items-center gap-1.5 text-body-sm font-medium text-navy hover:underline dark:text-gold"
            >
              <ArrowLeft className="h-4 w-4" aria-hidden="true" />
              Back to overview
            </button>
          </div>

          <div className="relative max-w-sm">
            <SearchIcon className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === "Enter") {
                  setPage(1);
                  fetchEntities(selectedType.type, { search, page: 1 });
                }
              }}
              placeholder={`Search ${selectedType.label.toLowerCase()}...`}
              className="h-10 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
            />
          </div>

          <Card>
            <Table
              columns={columns}
              rows={entities}
              rowKey={(e) => e.id}
              isLoading={isLoadingEntities}
              emptyTitle={`No ${selectedType.label.toLowerCase()} found`}
            />
          </Card>

          {meta && <Pagination meta={meta} onPageChange={setPage} />}
        </>
      )}

      <Modal open={!!editing} onClose={handleModalClose} title={editing?.label ?? "SEO Settings"} size="wide">
        {editing && selectedType && <SeoFieldsPanel type={selectedType.type} id={editing.id} canEdit={canEdit} />}
      </Modal>
    </div>
  );
}
