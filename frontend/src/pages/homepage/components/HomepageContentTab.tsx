import { useEffect, useState } from "react";
import { Button, Input, Textarea, useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { RepeatableItemsEditor } from "@/components/content-blocks/RepeatableItemsEditor";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { SettingsMap } from "@/types/settings";

interface HomepageContentTabProps {
  canEdit: boolean;
}

type ItemRecord = Record<string, string | number | null | undefined>;

/**
 * Welcome/Why Choose Us/Statistics/CTA/Footer Widgets — the homepage
 * sections with no dedicated content table (see HomepageController's
 * docblock on the backend). Reads/writes /admin/homepage-content, a
 * separate endpoint from /admin/settings so Marketing can edit this
 * without needing the Super-Admin-only Settings module.
 */
export function HomepageContentTab({ canEdit }: HomepageContentTabProps) {
  const { showToast } = useToast();
  const [content, setContent] = useState<SettingsMap>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    api.get<ApiResponse<SettingsMap>>(ENDPOINTS.homepage.content).then(({ data }) => {
      setContent(data.data);
      setIsLoading(false);
    });
  }, []);

  function setField(key: string, value: string | number | ItemRecord[] | null) {
    setContent((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      const payload: Record<string, string> = {
        welcome_heading: String(content.welcome_heading ?? ""),
        welcome_body: String(content.welcome_body ?? ""),
        welcome_media_id: content.welcome_media_id ? String(content.welcome_media_id) : "",
        why_choose_us_items: JSON.stringify(content.why_choose_us_items ?? []),
        statistics_items: JSON.stringify(content.statistics_items ?? []),
        cta_heading: String(content.cta_heading ?? ""),
        cta_body: String(content.cta_body ?? ""),
        cta_button_label: String(content.cta_button_label ?? ""),
        cta_button_url: String(content.cta_button_url ?? ""),
        footer_widgets: JSON.stringify(content.footer_widgets ?? []),
      };

      const { data } = await api.put<ApiResponse<SettingsMap>>(ENDPOINTS.homepage.content, { content: payload });
      setContent(data.data);
      showToast("Homepage content saved.", "success");
    } catch {
      showToast("Could not save homepage content.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  const whyChooseUsItems = (content.why_choose_us_items as ItemRecord[] | undefined) ?? [];
  const statisticsItems = (content.statistics_items as ItemRecord[] | undefined) ?? [];
  const footerWidgets = (content.footer_widgets as ItemRecord[] | undefined) ?? [];

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Welcome</h3>
        <Input label="Heading" value={String(content.welcome_heading ?? "")} onChange={(e) => setField("welcome_heading", e.target.value)} />
        <Textarea label="Body" value={String(content.welcome_body ?? "")} onChange={(e) => setField("welcome_body", e.target.value)} rows={4} />
        <MediaIdField
          label="Image"
          type="image"
          mediaId={content.welcome_media_id ? Number(content.welcome_media_id) : null}
          onChange={(id) => setField("welcome_media_id", id)}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Why Choose Us</h3>
        <RepeatableItemsEditor
          items={whyChooseUsItems}
          onChange={(items) => setField("why_choose_us_items", items)}
          addLabel="Add Feature"
          fields={[
            { key: "icon", label: "Icon (optional)", kind: "text" },
            { key: "title", label: "Title", kind: "text" },
            { key: "text", label: "Description", kind: "textarea" },
          ]}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Statistics</h3>
        <RepeatableItemsEditor
          items={statisticsItems}
          onChange={(items) => setField("statistics_items", items)}
          addLabel="Add Statistic"
          fields={[
            { key: "label", label: "Label", kind: "text" },
            { key: "value", label: "Value", kind: "text" },
            { key: "icon", label: "Icon (optional)", kind: "text" },
          ]}
        />
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">CTA</h3>
        <Input label="Heading" value={String(content.cta_heading ?? "")} onChange={(e) => setField("cta_heading", e.target.value)} />
        <Textarea label="Body" value={String(content.cta_body ?? "")} onChange={(e) => setField("cta_body", e.target.value)} rows={3} />
        <div className="grid grid-cols-2 gap-3">
          <Input label="Button Label" value={String(content.cta_button_label ?? "")} onChange={(e) => setField("cta_button_label", e.target.value)} />
          <Input label="Button URL" value={String(content.cta_button_url ?? "")} onChange={(e) => setField("cta_button_url", e.target.value)} />
        </div>
      </section>

      <hr className="border-[color:var(--color-border)]" />

      <section className="flex flex-col gap-4">
        <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Footer Widgets</h3>
        <RepeatableItemsEditor
          items={footerWidgets}
          onChange={(items) => setField("footer_widgets", items)}
          addLabel="Add Widget"
          fields={[
            { key: "heading", label: "Heading", kind: "text" },
            { key: "body", label: "Body", kind: "textarea" },
          ]}
        />
      </section>

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Changes
        </Button>
      )}
    </fieldset>
  );
}
