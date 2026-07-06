import { useEffect, useState } from "react";
import { Button, Input, Textarea, Switch, useToast } from "@/components/ui";
import { RichTextEditor } from "@/components/editor/RichTextEditor";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { TagInput } from "./TagInput";
import type { ApiCollection } from "@/types/api";
import type { BlogPost, BlogPostPayload, BlogPostStatus, BlogCategory } from "@/types/blog";

interface BlogPostContentTabProps {
  post: BlogPost;
  canEdit: boolean;
  onSave: (payload: BlogPostPayload) => Promise<void>;
}

/**
 * Author is shown read-only (set automatically to whoever created the
 * post) rather than a reassignable picker — this project has no Users
 * admin screen yet (Roadmap Stage 7: Access Control Finalization), so
 * there's nowhere to look up a different user to reassign to.
 */
export function BlogPostContentTab({ post, canEdit, onSave }: BlogPostContentTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<BlogPostPayload>({});
  const [categories, setCategories] = useState<BlogCategory[]>([]);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      title: post.title,
      slug: post.slug,
      excerpt: post.excerpt ?? "",
      body: post.body,
      category_id: post.category?.id ?? null,
      tags: post.tags.map((t) => t.name),
      status: post.status,
      published_at: post.published_at,
      is_featured: post.is_featured,
    });

    api.get<ApiCollection<BlogCategory>>(ENDPOINTS.blogCategories.admin(), { params: { per_page: 100 } }).then(({ data }) => {
      setCategories(data.data);
    });
  }, [post]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Post saved.", "success");
    } catch {
      showToast("Could not save. Check the title/slug and required fields.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Title" value={form.title ?? ""} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the title if left blank. Public URL: /blog/{slug}."
        value={form.slug ?? ""}
        onChange={(e) => setForm({ ...form, slug: e.target.value })}
      />
      <Textarea
        label="Excerpt"
        hint="Shown in listing cards and search results."
        value={form.excerpt ?? ""}
        onChange={(e) => setForm({ ...form, excerpt: e.target.value })}
        rows={2}
      />

      <RichTextEditor label="Body" value={form.body ?? ""} onChange={(html) => setForm({ ...form, body: html })} disabled={!canEdit} />

      <div className="grid grid-cols-2 gap-3">
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Category</span>
          <select
            value={form.category_id ?? ""}
            onChange={(e) => setForm({ ...form, category_id: e.target.value ? Number(e.target.value) : null })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="">None</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </label>

        <div className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Author</span>
          <p className="flex h-10 items-center text-body text-[color:var(--color-text)]">{post.author?.name ?? "—"}</p>
        </div>
      </div>

      <TagInput value={form.tags ?? []} onChange={(tags) => setForm({ ...form, tags })} disabled={!canEdit} />

      <div className="grid grid-cols-2 gap-3">
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Status</span>
          <select
            value={form.status ?? "draft"}
            onChange={(e) => setForm({ ...form, status: e.target.value as BlogPostStatus })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="scheduled">Scheduled</option>
            <option value="archived">Archived</option>
          </select>
        </label>

        <Input
          label="Publish Date/Time"
          type="datetime-local"
          hint="Required for Scheduled — the post goes live automatically at this time."
          value={form.published_at ? form.published_at.slice(0, 16) : ""}
          onChange={(e) => setForm({ ...form, published_at: e.target.value ? new Date(e.target.value).toISOString() : null })}
        />
      </div>

      <Switch label="Featured" checked={form.is_featured ?? false} onChange={(checked) => setForm({ ...form, is_featured: checked })} />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Post
        </Button>
      )}
    </fieldset>
  );
}
