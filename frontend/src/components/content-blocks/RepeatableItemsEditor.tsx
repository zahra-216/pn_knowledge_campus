import { Plus, Trash2 } from "lucide-react";
import { Button, Input, Textarea } from "@/components/ui";
import { MediaIdField } from "./MediaIdField";

type ItemRecord = Record<string, string | number | null | undefined>;

interface RepeatableField {
  key: string;
  label: string;
  kind: "text" | "textarea" | "media";
}

interface RepeatableItemsEditorProps {
  items: ItemRecord[];
  fields: RepeatableField[];
  onChange: (items: ItemRecord[]) => void;
  addLabel: string;
}

/**
 * FAQ, Statistics, Testimonials, and Partners blocks are all "an ordered
 * array of small objects with a handful of scalar fields" — the same
 * shape with different field names, so one generic row editor covers
 * all four instead of four near-duplicate components.
 */
export function RepeatableItemsEditor({ items, fields, onChange, addLabel }: RepeatableItemsEditorProps) {
  function updateItem(index: number, key: string, value: string | number | null) {
    const next = items.map((item, i) => (i === index ? { ...item, [key]: value } : item));
    onChange(next);
  }

  function addItem() {
    const blank: ItemRecord = {};
    fields.forEach((f) => {
      blank[f.key] = f.kind === "media" ? null : "";
    });
    onChange([...items, blank]);
  }

  function removeItem(index: number) {
    onChange(items.filter((_, i) => i !== index));
  }

  return (
    <div className="flex flex-col gap-3">
      {items.map((item, index) => (
        <div key={index} className="flex flex-col gap-3 rounded-md border border-[color:var(--color-border)] p-3">
          <div className="flex items-center justify-between">
            <span className="text-caption font-medium uppercase tracking-wide text-neutral-500">Item {index + 1}</span>
            <button type="button" onClick={() => removeItem(index)} aria-label={`Remove item ${index + 1}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          </div>

          {fields.map((field) =>
            field.kind === "media" ? (
              <MediaIdField
                key={field.key}
                label={field.label}
                type="image"
                mediaId={typeof item[field.key] === "number" ? (item[field.key] as number) : null}
                onChange={(mediaId) => updateItem(index, field.key, mediaId)}
              />
            ) : field.kind === "textarea" ? (
              <Textarea
                key={field.key}
                label={field.label}
                value={(item[field.key] as string) ?? ""}
                onChange={(e) => updateItem(index, field.key, e.target.value)}
                rows={3}
              />
            ) : (
              <Input
                key={field.key}
                label={field.label}
                value={(item[field.key] as string) ?? ""}
                onChange={(e) => updateItem(index, field.key, e.target.value)}
              />
            )
          )}
        </div>
      ))}

      <Button type="button" variant="secondary" onClick={addItem}>
        <Plus className="h-4 w-4" aria-hidden="true" />
        {addLabel}
      </Button>
    </div>
  );
}
