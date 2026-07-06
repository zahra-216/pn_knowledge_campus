import { useState } from "react";
import { Folder, FolderOpen, Plus, Trash2 } from "lucide-react";
import { cn } from "@/utils/cn";
import { usePermission } from "@/hooks/usePermission";
import type { MediaFolder } from "@/types/media";

interface FolderTreeProps {
  folders: MediaFolder[];
  activeFolderId: number | null;
  onSelect: (folderId: number | null) => void;
  onCreate: (name: string, parentId: number | null) => Promise<void>;
  onDelete: (folderId: number) => Promise<void>;
}

/**
 * UI/UX Design, Section 5.4/6.4 — Media Library's folder tree sidebar.
 * Development Roadmap, Milestone 1.
 */
export function FolderTree({ folders, activeFolderId, onSelect, onCreate, onDelete }: FolderTreeProps) {
  const { can } = usePermission();
  const [isCreating, setIsCreating] = useState(false);
  const [newName, setNewName] = useState("");

  async function handleCreate() {
    if (!newName.trim()) return;
    await onCreate(newName.trim(), null);
    setNewName("");
    setIsCreating(false);
  }

  return (
    <div className="flex w-56 flex-shrink-0 flex-col gap-1 border-r border-[color:var(--color-border)] pr-3">
      <button
        type="button"
        onClick={() => onSelect(null)}
        className={cn(
          "flex items-center gap-2 rounded px-2 py-1.5 text-left text-body-sm",
          activeFolderId === null ? "bg-navy/10 font-medium text-navy" : "text-[color:var(--color-text)] hover:bg-black/5 dark:hover:bg-white/5"
        )}
      >
        <FolderOpen className="h-4 w-4" aria-hidden="true" />
        All Files
      </button>

      {folders.map((folder) => (
        <div key={folder.id} className="group flex items-center gap-1">
          <button
            type="button"
            onClick={() => onSelect(folder.id)}
            className={cn(
              "flex flex-1 items-center gap-2 rounded px-2 py-1.5 text-left text-body-sm",
              activeFolderId === folder.id
                ? "bg-navy/10 font-medium text-navy"
                : "text-[color:var(--color-text)] hover:bg-black/5 dark:hover:bg-white/5"
            )}
          >
            <Folder className="h-4 w-4 flex-shrink-0" aria-hidden="true" />
            <span className="truncate">{folder.name}</span>
            {folder.media_count !== null && (
              <span className="ml-auto text-caption text-neutral-500">{folder.media_count}</span>
            )}
          </button>
          {can("media.delete") && (
            <button
              type="button"
              onClick={() => onDelete(folder.id)}
              aria-label={`Delete ${folder.name} folder`}
              className="hidden rounded p-1 text-neutral-400 hover:text-danger group-hover:block"
            >
              <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
            </button>
          )}
        </div>
      ))}

      {can("media.create") && (
        <div className="mt-2">
          {isCreating ? (
            <div className="flex gap-1">
              <input
                autoFocus
                value={newName}
                onChange={(e) => setNewName(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && handleCreate()}
                placeholder="Folder name"
                className="h-8 flex-1 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-2 text-body-sm"
              />
              <button type="button" onClick={handleCreate} className="text-body-sm text-navy">
                Add
              </button>
            </div>
          ) : (
            <button
              type="button"
              onClick={() => setIsCreating(true)}
              className="flex items-center gap-2 rounded px-2 py-1.5 text-body-sm text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
            >
              <Plus className="h-4 w-4" aria-hidden="true" />
              New Folder
            </button>
          )}
        </div>
      )}
    </div>
  );
}
