import { useState } from "react";
import { Image } from "lucide-react";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Input } from "@/components/ui";
import { useMediaLibrary } from "@/hooks/useMediaLibrary";
import { usePermission } from "@/hooks/usePermission";
import { useToast } from "@/components/ui";
import { FolderTree } from "./components/FolderTree";
import { MediaGrid } from "./components/MediaGrid";
import { UploadDropzone } from "./components/UploadDropzone";
import { MediaDetailPanel } from "./components/MediaDetailPanel";
import { MediaPreviewModal } from "./components/MediaPreviewModal";
import type { MediaItem } from "@/types/media";

/**
 * Development Roadmap, Milestone 1 — "Fully functional Media Library,
 * usable end-to-end by every later module." UI/UX Design, Section 5.4.
 */
export function MediaLibrary() {
  const { can } = usePermission();
  const { showToast } = useToast();
  const [activeFolderId, setActiveFolderId] = useState<number | null>(null);
  const [search, setSearch] = useState("");
  const [selected, setSelected] = useState<MediaItem | null>(null);
  const [previewing, setPreviewing] = useState<MediaItem | null>(null);
  const canView = can("media.view");

  const { folders, media, isLoading, isUploading, upload, updateMedia, deleteMedia, replaceMedia, createFolder, deleteFolder } =
    useMediaLibrary({
      folderId: activeFolderId,
      search: search || undefined,
      enabled: canView,
    });

  async function handleUpload(file: File, altText: string | null) {
    try {
      await upload(file, altText, activeFolderId);
      showToast("File uploaded.", "success");
    } catch {
      showToast("Upload failed. Check the file type and size.", "error");
    }
  }

  async function handleDeleteFolder(folderId: number) {
    try {
      await deleteFolder(folderId);
      if (activeFolderId === folderId) setActiveFolderId(null);
    } catch {
      showToast("This folder still contains files or subfolders.", "error");
    }
  }

  async function handleDeleteMedia(id: number) {
    await deleteMedia(id);
    setSelected(null);
  }

  async function handleReplaceMedia(id: number, file: File, altText: string | null) {
    const updated = await replaceMedia(id, file, altText);
    setSelected(updated);
    return updated;
  }

  if (!canView) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Media Library" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Image className="h-4 w-4" aria-hidden="true" />
            You don't have access to the Media Library.
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Media Library" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Media Library</h1>
        <Input
          placeholder="Search files..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-64"
          aria-label="Search media"
        />
      </div>

      <Card className="p-0">
        <div className="flex gap-4 p-4">
          <FolderTree
            folders={folders}
            activeFolderId={activeFolderId}
            onSelect={setActiveFolderId}
            onCreate={createFolder}
            onDelete={handleDeleteFolder}
          />

          <div className="flex flex-1 flex-col gap-4">
            {can("media.create") && <UploadDropzone onUpload={handleUpload} isUploading={isUploading} />}
            <MediaGrid
              media={media}
              isLoading={isLoading}
              selectedId={selected?.id}
              onSelect={setSelected}
              onPreview={setPreviewing}
            />
          </div>

          {selected && (
            <MediaDetailPanel
              item={selected}
              folders={folders}
              onUpdate={updateMedia}
              onDelete={handleDeleteMedia}
              onReplace={handleReplaceMedia}
              onPreview={setPreviewing}
              onClose={() => setSelected(null)}
            />
          )}
        </div>
      </Card>

      <MediaPreviewModal item={previewing} onClose={() => setPreviewing(null)} />
    </div>
  );
}
