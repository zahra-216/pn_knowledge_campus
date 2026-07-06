import { useEffect, useRef, useState } from "react";
import { Eye, RefreshCw, Trash2 } from "lucide-react";
import { Button, Input, useToast } from "@/components/ui";
import { usePermission } from "@/hooks/usePermission";
import type { MediaFolder, MediaItem } from "@/types/media";

interface MediaDetailPanelProps {
  item: MediaItem;
  folders: MediaFolder[];
  onUpdate: (id: number, payload: { alt_text?: string | null; folder_id?: number | null }) => Promise<MediaItem>;
  onDelete: (id: number) => Promise<void>;
  onReplace: (id: number, file: File, altText: string | null) => Promise<MediaItem>;
  onPreview: (item: MediaItem) => void;
  onClose: () => void;
}

/**
 * UI/UX Design, Section 5.4 — the right-hand side panel for a selected
 * asset: edit alt_text/folder, preview, replace, or delete. API Design,
 * Section 8.6; replace/preview are Media Library hardening additions.
 */
export function MediaDetailPanel({ item, folders, onUpdate, onDelete, onReplace, onPreview, onClose }: MediaDetailPanelProps) {
  const { can } = usePermission();
  const { showToast } = useToast();
  const [altText, setAltText] = useState(item.alt_text ?? "");
  const [folderId, setFolderId] = useState<number | "">(item.folder_id ?? "");
  const [isSaving, setIsSaving] = useState(false);
  const [isReplacing, setIsReplacing] = useState(false);
  const replaceInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    setAltText(item.alt_text ?? "");
    setFolderId(item.folder_id ?? "");
  }, [item]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onUpdate(item.id, { alt_text: altText || null, folder_id: folderId === "" ? null : folderId });
    } finally {
      setIsSaving(false);
    }
  }

  async function handleReplaceFileChosen(file: File) {
    setIsReplacing(true);
    try {
      await onReplace(item.id, file, altText || null);
      showToast("File replaced.", "success");
    } catch {
      showToast("Could not replace this file. Check the file type, size, and alt text.", "error");
    } finally {
      setIsReplacing(false);
      if (replaceInputRef.current) replaceInputRef.current.value = "";
    }
  }

  return (
    <div className="flex w-72 flex-shrink-0 flex-col gap-4 border-l border-[color:var(--color-border)] pl-4">
      <div className="flex items-center justify-between">
        <p className="truncate text-body-sm font-medium text-[color:var(--color-text)]">{item.name}</p>
        <button type="button" onClick={onClose} className="text-caption text-neutral-500 hover:underline">
          Close
        </button>
      </div>

      {item.mime_type?.startsWith("image/") ? (
        <img src={item.thumb_url ?? item.url} alt={item.alt_text ?? ""} className="rounded-md" />
      ) : (
        <a href={item.url} target="_blank" rel="noreferrer" className="text-body-sm text-info hover:underline">
          View file
        </a>
      )}

      <Button variant="secondary" size="sm" onClick={() => onPreview(item)}>
        <Eye className="h-4 w-4" aria-hidden="true" />
        Preview
      </Button>

      <dl className="text-caption text-neutral-500">
        <div className="flex justify-between py-0.5">
          <dt>Type</dt>
          <dd>{item.mime_type}</dd>
        </div>
        <div className="flex justify-between py-0.5">
          <dt>Size</dt>
          <dd>{(item.size / 1024).toFixed(1)} KB</dd>
        </div>
        {item.width && item.height && (
          <div className="flex justify-between py-0.5">
            <dt>Dimensions</dt>
            <dd>
              {item.width} × {item.height}px
            </dd>
          </div>
        )}
      </dl>

      {can("media.edit") && (
        <>
          <Input label="Alt text" value={altText} onChange={(e) => setAltText(e.target.value)} />

          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Folder</span>
            <select
              value={folderId}
              onChange={(e) => setFolderId(e.target.value ? Number(e.target.value) : "")}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="">No folder</option>
              {folders.map((folder) => (
                <option key={folder.id} value={folder.id}>
                  {folder.name}
                </option>
              ))}
            </select>
          </label>

          <Button onClick={handleSave} isLoading={isSaving}>
            Save
          </Button>

          <Button
            variant="secondary"
            isLoading={isReplacing}
            onClick={() => replaceInputRef.current?.click()}
          >
            <RefreshCw className="h-4 w-4" aria-hidden="true" />
            Replace File
          </Button>
          <input
            ref={replaceInputRef}
            type="file"
            className="hidden"
            onChange={(e) => {
              const file = e.target.files?.[0];
              if (file) handleReplaceFileChosen(file);
            }}
          />
        </>
      )}

      {can("media.delete") && (
        <Button variant="danger" onClick={() => onDelete(item.id)} className="mt-auto">
          <Trash2 className="h-4 w-4" aria-hidden="true" />
          Delete
        </Button>
      )}
    </div>
  );
}
