import { useEffect, useState } from "react";
import { Modal, Button, Input, Textarea, Switch } from "@/components/ui";
import type { BlockType, PageBlock, PageBlockPayload } from "@/types/page";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { RepeatableItemsEditor } from "@/components/content-blocks/RepeatableItemsEditor";

interface PageBlockFormProps {
  open: boolean;
  block: PageBlock | null;
  onClose: () => void;
  onSave: (payload: PageBlockPayload) => Promise<void>;
}

const BLOCK_TYPE_LABELS: Record<BlockType, string> = {
  hero: "Hero",
  text: "Text",
  rich_text: "Rich Text",
  image: "Image",
  gallery: "Gallery",
  video: "Video",
  cta: "Call to Action",
  faq: "FAQ",
  statistics: "Statistics",
  testimonials: "Testimonials",
  partners: "Partners",
  chairman_message: "Chairman Message",
  management_board: "Management Board",
};

const DEFAULT_DATA: Record<BlockType, Record<string, unknown>> = {
  hero: { heading: "", subheading: "", media_id: null, cta_label: "", cta_url: "", alignment: "center" },
  text: { body: "" },
  rich_text: { body: "" },
  image: { media_id: null, caption: "" },
  gallery: { media_ids: [] },
  video: { source: "youtube", media_id: null, url: "", caption: "" },
  cta: { heading: "", body: "", button_label: "", button_url: "", style: "primary" },
  faq: { items: [] },
  statistics: { items: [] },
  testimonials: { items: [] },
  partners: { items: [] },
  chairman_message: { heading: "", name: "", role: "", message: "", media_id: null },
  management_board: { items: [] },
};

/**
 * One modal, dispatching to a different set of fields per block_type
 * (Database Design's own justification for page_blocks.data being JSON:
 * 11 genuinely different shapes). block_type is fixed once a block
 * exists — changing it would silently orphan its old data shape, so
 * that requires deleting and re-adding the block instead.
 */
export function PageBlockForm({ open, block, onClose, onSave }: PageBlockFormProps) {
  const [blockType, setBlockType] = useState<BlockType>("text");
  const [data, setData] = useState<Record<string, unknown>>(DEFAULT_DATA.text);
  const [isActive, setIsActive] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (block) {
      setBlockType(block.block_type);
      setData(block.data);
      setIsActive(block.is_active);
    } else {
      setBlockType("text");
      setData(DEFAULT_DATA.text);
      setIsActive(true);
    }
  }, [block, open]);

  function setField(key: string, value: unknown) {
    setData((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave({ block_type: blockType, data, is_active: isActive });
      onClose();
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={block ? `Edit ${BLOCK_TYPE_LABELS[blockType]} Block` : "New Block"}
      size="wide"
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={handleSave} isLoading={isSaving}>
            Save
          </Button>
        </>
      }
    >
      <div className="flex flex-col gap-4">
        {!block && (
          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Block Type</span>
            <select
              value={blockType}
              onChange={(e) => {
                const next = e.target.value as BlockType;
                setBlockType(next);
                setData(DEFAULT_DATA[next]);
              }}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              {(Object.keys(BLOCK_TYPE_LABELS) as BlockType[]).map((type) => (
                <option key={type} value={type}>
                  {BLOCK_TYPE_LABELS[type]}
                </option>
              ))}
            </select>
          </label>
        )}

        <BlockFields blockType={blockType} data={data} setField={setField} />

        <Switch label="Active (visible on the public page)" checked={isActive} onChange={setIsActive} />
      </div>
    </Modal>
  );
}

function BlockFields({
  blockType,
  data,
  setField,
}: {
  blockType: BlockType;
  data: Record<string, unknown>;
  setField: (key: string, value: unknown) => void;
}) {
  const str = (key: string) => (data[key] as string) ?? "";
  const num = (key: string) => (typeof data[key] === "number" ? (data[key] as number) : null);

  switch (blockType) {
    case "hero":
      return (
        <>
          <Input label="Heading" value={str("heading")} onChange={(e) => setField("heading", e.target.value)} required />
          <Input label="Subheading" value={str("subheading")} onChange={(e) => setField("subheading", e.target.value)} />
          <MediaIdField label="Background Image" type="image" mediaId={num("media_id")} onChange={(id) => setField("media_id", id)} />
          <div className="grid grid-cols-2 gap-3">
            <Input label="CTA Label" value={str("cta_label")} onChange={(e) => setField("cta_label", e.target.value)} />
            <Input label="CTA URL" value={str("cta_url")} onChange={(e) => setField("cta_url", e.target.value)} />
          </div>
          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Alignment</span>
            <select
              value={str("alignment") || "center"}
              onChange={(e) => setField("alignment", e.target.value)}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </label>
        </>
      );

    case "text":
    case "rich_text":
      return (
        <Textarea
          label={blockType === "rich_text" ? "Body (HTML)" : "Body"}
          hint={blockType === "rich_text" ? "Raw HTML — this project has no WYSIWYG editor dependency yet." : undefined}
          value={str("body")}
          onChange={(e) => setField("body", e.target.value)}
          rows={6}
          required
        />
      );

    case "image":
      return (
        <>
          <MediaIdField label="Image" type="image" mediaId={num("media_id")} onChange={(id) => setField("media_id", id)} />
          <Input label="Caption" value={str("caption")} onChange={(e) => setField("caption", e.target.value)} />
        </>
      );

    case "gallery":
      return <GalleryField mediaIds={(data.media_ids as number[]) ?? []} onChange={(ids) => setField("media_ids", ids)} />;

    case "video":
      return (
        <>
          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Source</span>
            <select
              value={str("source") || "youtube"}
              onChange={(e) => setField("source", e.target.value)}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="youtube">YouTube</option>
              <option value="vimeo">Vimeo</option>
              <option value="upload">Uploaded file (Media Library)</option>
            </select>
          </label>
          {data.source === "upload" ? (
            <MediaIdField label="Video File" type="video" mediaId={num("media_id")} onChange={(id) => setField("media_id", id)} />
          ) : (
            <Input label="Video URL" value={str("url")} onChange={(e) => setField("url", e.target.value)} />
          )}
          <Input label="Caption" value={str("caption")} onChange={(e) => setField("caption", e.target.value)} />
        </>
      );

    case "chairman_message":
      return (
        <>
          <Input label="Heading (optional)" value={str("heading")} onChange={(e) => setField("heading", e.target.value)} />
          <Input label="Name" value={str("name")} onChange={(e) => setField("name", e.target.value)} required />
          <Input
            label="Role"
            value={str("role")}
            onChange={(e) => setField("role", e.target.value)}
            hint='Include the word "Manager" to render this as the Manager layout instead of Chairman.'
          />
          <Textarea label="Message" value={str("message")} onChange={(e) => setField("message", e.target.value)} rows={6} required />
          <MediaIdField label="Photo" type="image" mediaId={num("media_id")} onChange={(id) => setField("media_id", id)} />
        </>
      );

    case "cta":
      return (
        <>
          <Input label="Heading" value={str("heading")} onChange={(e) => setField("heading", e.target.value)} required />
          <Textarea label="Body" value={str("body")} onChange={(e) => setField("body", e.target.value)} rows={3} />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Button Label" value={str("button_label")} onChange={(e) => setField("button_label", e.target.value)} required />
            <Input label="Button URL" value={str("button_url")} onChange={(e) => setField("button_url", e.target.value)} required />
          </div>
          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Style</span>
            <select
              value={str("style") || "primary"}
              onChange={(e) => setField("style", e.target.value)}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
            </select>
          </label>
        </>
      );

    case "faq":
      return (
        <RepeatableItemsEditor
          items={(data.items as Record<string, string>[]) ?? []}
          onChange={(items) => setField("items", items)}
          addLabel="Add Question"
          fields={[
            { key: "question", label: "Question", kind: "text" },
            { key: "answer", label: "Answer", kind: "textarea" },
          ]}
        />
      );

    case "statistics":
      return (
        <RepeatableItemsEditor
          items={(data.items as Record<string, string>[]) ?? []}
          onChange={(items) => setField("items", items)}
          addLabel="Add Statistic"
          fields={[
            { key: "label", label: "Label", kind: "text" },
            { key: "value", label: "Value", kind: "text" },
            { key: "icon", label: "Icon (optional)", kind: "text" },
          ]}
        />
      );

    case "testimonials":
      return (
        <RepeatableItemsEditor
          items={(data.items as Record<string, string | number | null>[]) ?? []}
          onChange={(items) => setField("items", items)}
          addLabel="Add Testimonial"
          fields={[
            { key: "quote", label: "Quote", kind: "textarea" },
            { key: "name", label: "Name", kind: "text" },
            { key: "role", label: "Role (optional)", kind: "text" },
            { key: "avatar_media_id", label: "Avatar (optional)", kind: "media" },
          ]}
        />
      );

    case "partners":
      return (
        <RepeatableItemsEditor
          items={(data.items as Record<string, string | number | null>[]) ?? []}
          onChange={(items) => setField("items", items)}
          addLabel="Add Partner"
          fields={[
            { key: "name", label: "Name", kind: "text" },
            { key: "logo_media_id", label: "Logo", kind: "media" },
            { key: "url", label: "URL (optional)", kind: "text" },
          ]}
        />
      );

      case "management_board":
        return (
          <RepeatableItemsEditor
            items={(data.items as Record<string, string | number | null>[]) ?? []}
            onChange={(items) => setField("items", items)}
            addLabel="Add Director"
            fields={[
              { key: "name", label: "Name", kind: "text" },
              { key: "position", label: "Position", kind: "text" },
              { key: "photo_media_id", label: "Photo", kind: "media" },
            ]}
          />
        );

    default:
      return null;
  }
}

function GalleryField({ mediaIds, onChange }: { mediaIds: number[]; onChange: (ids: number[]) => void }) {
  return (
    <div className="flex flex-col gap-2">
      <span className="text-body-sm font-medium text-[color:var(--color-text)]">Images</span>
      <div className="flex flex-wrap gap-2">
        {mediaIds.map((id) => (
          <span key={id} className="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-3 py-1 text-caption">
            Media #{id}
            <button type="button" aria-label={`Remove media ${id}`} onClick={() => onChange(mediaIds.filter((m) => m !== id))}>
              ×
            </button>
          </span>
        ))}
      </div>
      <MediaIdField
        label="Add an image"
        type="image"
        mediaId={null}
        onChange={(id) => {
          if (id && !mediaIds.includes(id)) onChange([...mediaIds, id]);
        }}
      />
    </div>
  );
}
