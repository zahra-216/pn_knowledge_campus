import { useEffect, useState } from "react";
import { Button, Input, Textarea, Switch, useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { SeoableType, SeoMeta, SeoMetaPayload } from "@/types/seo";

interface SeoFieldsPanelProps {
  type: SeoableType;
  id: number;
  canEdit: boolean;
}

const EMPTY: SeoMetaPayload = { robots_index: true, robots_follow: true };

/**
 * Generic per-entity SEO editor (API Design, Section 8.8) — reads/writes
 * /admin/seo/{type}/{id}, the infrastructure Milestone 1 built before
 * any seoable content model existed. Faculty is the first real screen to
 * use it; Department/Course/News/etc. reuse this same component once
 * they register their own config('seo.seoable_types') entry.
 */
export function SeoFieldsPanel({ type, id, canEdit }: SeoFieldsPanelProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<SeoMetaPayload>(EMPTY);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const media = useResolvedMedia([form.og_image_media_id, form.twitter_image_media_id]);

  useEffect(() => {
    setIsLoading(true);
    api.get<ApiResponse<SeoMeta | null>>(ENDPOINTS.seo.admin(type, id)).then(({ data }) => {
      setForm(
        data.data ?? EMPTY
      );
      setIsLoading(false);
    });
  }, [type, id]);

  async function handleSave() {
    setIsSaving(true);
    try {
      const { data } = await api.put<ApiResponse<SeoMeta>>(ENDPOINTS.seo.admin(type, id), form);
      setForm(data.data);
      showToast("SEO settings saved.", "success");
    } catch {
      showToast("Could not save SEO settings.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input
        label="SEO Title"
        hint="Shown in search results and browser tabs. Falls back to the entity's own title if left blank."
        value={form.seo_title ?? ""}
        onChange={(e) => setForm({ ...form, seo_title: e.target.value })}
      />
      <Textarea
        label="Meta Description"
        value={form.meta_description ?? ""}
        onChange={(e) => setForm({ ...form, meta_description: e.target.value })}
        rows={3}
      />
      <Input label="Keywords" hint="Comma-separated." value={form.keywords ?? ""} onChange={(e) => setForm({ ...form, keywords: e.target.value })} />
      <Input label="Canonical URL" value={form.canonical_url ?? ""} onChange={(e) => setForm({ ...form, canonical_url: e.target.value })} />
      <Input
        label="Schema Type"
        hint="e.g. 'CollegeOrUniversity', 'Course' — used for structured data."
        value={form.schema_type ?? ""}
        onChange={(e) => setForm({ ...form, schema_type: e.target.value })}
      />

      <hr className="border-[color:var(--color-border)]" />

      <Input label="Open Graph Title" value={form.og_title ?? ""} onChange={(e) => setForm({ ...form, og_title: e.target.value })} />
      <Textarea
        label="Open Graph Description"
        value={form.og_description ?? ""}
        onChange={(e) => setForm({ ...form, og_description: e.target.value })}
        rows={2}
      />
      <MediaIdField
        label="Open Graph Image"
        type="image"
        mediaId={form.og_image_media_id ?? null}
        previewUrl={form.og_image_media_id ? media.get(form.og_image_media_id)?.thumb_url : null}
        onChange={(id) => setForm({ ...form, og_image_media_id: id })}
      />
      <Input label="Twitter Title" value={form.twitter_title ?? ""} onChange={(e) => setForm({ ...form, twitter_title: e.target.value })} />
      <Textarea
        label="Twitter Description"
        value={form.twitter_description ?? ""}
        onChange={(e) => setForm({ ...form, twitter_description: e.target.value })}
        rows={2}
      />
      <MediaIdField
        label="Twitter Card Image"
        type="image"
        mediaId={form.twitter_image_media_id ?? null}
        previewUrl={form.twitter_image_media_id ? media.get(form.twitter_image_media_id)?.thumb_url : null}
        onChange={(id) => setForm({ ...form, twitter_image_media_id: id })}
      />

      <Switch
        label="Allow search engines to index this page"
        checked={form.robots_index ?? true}
        onChange={(checked) => setForm({ ...form, robots_index: checked })}
      />
      <Switch
        label="Allow search engines to follow links on this page"
        checked={form.robots_follow ?? true}
        onChange={(checked) => setForm({ ...form, robots_follow: checked })}
      />

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save SEO Settings
        </Button>
      )}
    </fieldset>
  );
}
