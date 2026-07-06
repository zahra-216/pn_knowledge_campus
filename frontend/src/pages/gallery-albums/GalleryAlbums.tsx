import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { GalleryAlbum } from "@/types/gallery";

/**
 * Gallery Album Management — SRS Permission Matrix, "Gallery" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access. No Publish button — visibility is just the
 * is_active flag, set from the editor's Details tab directly (see
 * GalleryAlbumPolicy's docblock).
 */
export function GalleryAlbums() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("gallery.create");
  const canDelete = can("gallery.delete");

  const [albums, setAlbums] = useState<GalleryAlbum[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchAlbums = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<GalleryAlbum>>(ENDPOINTS.galleryAlbums.admin(), { params: { per_page: 100 } });
      setAlbums(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("gallery.view")) return;
    fetchAlbums();
  }, [fetchAlbums, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<GalleryAlbum>>(ENDPOINTS.galleryAlbums.admin(), { title: "New Album" });
      navigate(`/admin/gallery-albums/${data.data.id}`);
    } catch {
      showToast("Could not create a new album.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(album: GalleryAlbum) {
    await api.delete(ENDPOINTS.galleryAlbums.admin(album.id));
    await fetchAlbums();
  }

  if (!can("gallery.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Gallery" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Gallery Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<GalleryAlbum>[] = [
    {
      key: "cover",
      header: "",
      render: (a) =>
        a.cover_url ? (
          <img src={a.cover_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "title", header: "Title", render: (a) => a.title },
    { key: "items_count", header: "Items", render: (a) => a.items_count ?? 0 },
    { key: "order", header: "Order", render: (a) => a.order },
    { key: "status", header: "Status", render: (a) => <Badge tone={a.is_active ? "success" : "neutral"}>{a.is_active ? "Active" : "Inactive"}</Badge> },
    {
      key: "actions",
      header: "",
      render: (a) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/gallery-albums/${a.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(a)} aria-label={`Delete ${a.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Gallery" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Gallery</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Album
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={albums}
          rowKey={(a) => a.id}
          isLoading={isLoading}
          emptyTitle="No albums yet"
          emptyDescription="Add your first photo or video album."
        />
      </Card>
    </div>
  );
}
