import { useEffect, useState } from "react";
import { X } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiCollection } from "@/types/api";
import type { Tag } from "@/types/blog";

interface TagInputProps {
  value: string[];
  onChange: (tags: string[]) => void;
  disabled?: boolean;
}

/**
 * Free-text tag chips with autocomplete against existing tags (native
 * <input list>/<datalist>, no new dependency). Typing a name that
 * doesn't match an existing tag is fine — BlogPostController creates it
 * on save via Tag::firstOrCreate(). The standalone /admin/tags CRUD
 * screen (TagController) is for renaming/deleting tags directly.
 */
export function TagInput({ value, onChange, disabled }: TagInputProps) {
  const [draft, setDraft] = useState("");
  const [existingTags, setExistingTags] = useState<Tag[]>([]);

  useEffect(() => {
    api.get<ApiCollection<Tag>>(ENDPOINTS.tags.admin(), { params: { per_page: 100 } }).then(({ data }) => {
      setExistingTags(data.data);
    });
  }, []);

  function addTag(name: string) {
    const trimmed = name.trim();
    if (!trimmed || value.some((t) => t.toLowerCase() === trimmed.toLowerCase())) {
      setDraft("");
      return;
    }
    onChange([...value, trimmed]);
    setDraft("");
  }

  function removeTag(name: string) {
    onChange(value.filter((t) => t !== name));
  }

  return (
    <div className="flex flex-col gap-1.5">
      <span className="text-body-sm font-medium text-[color:var(--color-text)]">Tags</span>

      <div className="flex flex-wrap items-center gap-1.5 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] p-2">
        {value.map((tag) => (
          <span
            key={tag}
            className="flex items-center gap-1 rounded-full bg-[color:var(--color-surface-alt)] px-2.5 py-1 text-caption text-[color:var(--color-text)]"
          >
            {tag}
            {!disabled && (
              <button type="button" onClick={() => removeTag(tag)} aria-label={`Remove ${tag}`}>
                <X className="h-3 w-3 text-neutral-400 hover:text-danger" aria-hidden="true" />
              </button>
            )}
          </span>
        ))}

        {!disabled && (
          <input
            list="tag-suggestions"
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter" || e.key === ",") {
                e.preventDefault();
                addTag(draft);
              }
            }}
            onBlur={() => addTag(draft)}
            placeholder="Type a tag and press Enter"
            className="min-w-[160px] flex-1 bg-transparent text-body outline-none"
          />
        )}
      </div>

      <datalist id="tag-suggestions">
        {existingTags.map((tag) => (
          <option key={tag.id} value={tag.name} />
        ))}
      </datalist>
    </div>
  );
}
