import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, Badge, type TabItem } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import { GalleryAlbumDetailsTab } from "./components/GalleryAlbumDetailsTab";
import { GalleryItemsTab } from "./components/GalleryItemsTab";
import type { ApiResponse } from "@/types/api";
import type { GalleryAlbum, GalleryAlbumPayload } from "@/types/gallery";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "items", label: "Items" },
];

/** No SEO tab — Gallery Albums is deliberately excluded from SEO (see the backend migration's docblock). */
export function GalleryAlbumEditor() {
  const { id } = useParams<{ id: string }>();
  const albumId = Number(id);
  const { can } = usePermission();
  const canEdit = can("gallery.edit");

  const [activeTab, setActiveTab] = useState("details");
  const [album, setAlbum] = useState<GalleryAlbum | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchAlbum = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<GalleryAlbum>>(ENDPOINTS.galleryAlbums.admin(albumId));
      setAlbum(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [albumId]);

  useEffect(() => {
    if (!can("gallery.view")) return;
    fetchAlbum();
  }, [fetchAlbum, can]);

  async function handleSave(payload: GalleryAlbumPayload) {
    const { data } = await api.put<ApiResponse<GalleryAlbum>>(ENDPOINTS.galleryAlbums.admin(albumId), payload);
    setAlbum(data.data);
  }

  if (!can("gallery.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Gallery", to: "/admin/gallery-albums" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Gallery Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !album) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Gallery", to: "/admin/gallery-albums" }, { label: album.title }]} />

      <div className="flex items-center gap-3">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{album.title}</h1>
        <Badge tone={album.is_active ? "success" : "neutral"}>{album.is_active ? "Active" : "Inactive"}</Badge>
      </div>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && <GalleryAlbumDetailsTab album={album} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "items" && <GalleryItemsTab album={album} canEdit={canEdit} onRefresh={fetchAlbum} />}
        </Tabs>
      </Card>
    </div>
  );
}
