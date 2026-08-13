import { useCallback, useEffect, useState } from "react";
import { Plus, Lock, Trash2, FileText } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { DownloadForm } from "./components/DownloadForm";
import type { ApiCollection } from "@/types/api";
import type { Download, DownloadPayload } from "@/types/download";

function formatSize(bytes: number | null): string {
  if (!bytes) return "—";
  const kb = bytes / 1024;
  if (kb < 1024) return `${kb.toFixed(0)} KB`;
  return `${(kb / 1024).toFixed(1)} MB`;
}

/**
 * Downloads Catalog Management (Milestone 18) — SRS Permission Matrix,
 * "Downloads" row: Super Admin/Administrator = Full; Content
 * Editor/Marketing = Create/Edit; Admissions = no access. Distinct from
 * a Course's own downloads tab (its own endpoint, its own permission
 * scope under courses.*).
 */
export function Downloads() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("downloads.create");
  const canDelete = can("downloads.delete");

  const [downloads, setDownloads] = useState<Download[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; download: Download | null }>({ open: false, download: null });

  const fetchDownloads = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<Download>>(ENDPOINTS.downloads.admin(), { params: { per_page: 100 } });
      setDownloads(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("downloads.view")) return;
    fetchDownloads();
  }, [fetchDownloads, can]);

  async function handleSave(payload: DownloadPayload) {
    try {
      if (formState.download) {
        await api.put(ENDPOINTS.downloads.admin(formState.download.id), payload);
      } else {
        await api.post(ENDPOINTS.downloads.admin(), payload);
      }
      showToast("Download saved.", "success");
      await fetchDownloads();
    } catch {
      showToast("Could not save this download.", "error");
    }
  }

  async function handleDelete(download: Download) {
    await api.delete(ENDPOINTS.downloads.admin(download.id));
    await fetchDownloads();
  }

  /**
   * A gated download's file_url points at the Sanctum-authenticated
   * /admin/downloads/{id}/file preview route (see DownloadResource's
   * docblock) — a plain `<a href>` wouldn't carry the bearer token, the
   * same reason ApplicationDetail.tsx fetches its own private-disk
   * documents as a blob instead of linking directly.
   */
  async function handlePreview(download: Download) {
    try {
      const { data } = await api.get(download.file_url as string, { responseType: "blob" });
      const url = URL.createObjectURL(data as Blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = download.file_name ?? download.title;
      link.click();
      URL.revokeObjectURL(url);
    } catch {
      showToast("Could not open this file.", "error");
    }
  }

  if (!can("downloads.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Downloads" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Downloads.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<Download>[] = [
    {
      key: "file",
      header: "",
      render: (d) =>
        d.file_url ? (
          d.requires_inquiry ? (
            <button type="button" onClick={() => handlePreview(d)} aria-label={`Open ${d.file_name}`}>
              <FileText className="h-8 w-8 text-navy dark:text-gold" />
            </button>
          ) : (
            <a href={d.file_url} target="_blank" rel="noopener noreferrer" aria-label={`Open ${d.file_name}`}>
              <FileText className="h-8 w-8 text-navy dark:text-gold" />
            </a>
          )
        ) : (
          <FileText className="h-8 w-8 text-neutral-300" />
        ),
    },
    { key: "title", header: "Title", render: (d) => d.title },
    { key: "category", header: "Category", render: (d) => d.category?.name ?? "—" },
    { key: "size", header: "Size", render: (d) => formatSize(d.file_size) },
    { key: "order", header: "Order", render: (d) => d.order },
    { key: "downloads", header: "Downloads", render: (d) => d.download_count },
    {
      key: "gated",
      header: "Gated",
      render: (d) => (d.requires_inquiry ? <Badge tone="warning">Requires form</Badge> : "—"),
    },
    { key: "status", header: "Status", render: (d) => <Badge tone={d.is_active ? "success" : "neutral"}>{d.is_active ? "Active" : "Inactive"}</Badge> },
    {
      key: "actions",
      header: "",
      render: (d) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => setFormState({ open: true, download: d })} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(d)} aria-label={`Delete ${d.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Downloads" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Downloads</h1>
        {canCreate && (
          <Button onClick={() => setFormState({ open: true, download: null })}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Download
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={downloads}
          rowKey={(d) => d.id}
          isLoading={isLoading}
          emptyTitle="No downloads yet"
          emptyDescription="Add your first prospectus, form, or brochure."
        />
      </Card>

      <DownloadForm
        open={formState.open}
        download={formState.download}
        onClose={() => setFormState({ open: false, download: null })}
        onSave={handleSave}
      />
    </div>
  );
}
