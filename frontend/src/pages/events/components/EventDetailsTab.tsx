import { useEffect, useState } from "react";
import { Button, Input, Textarea, Switch, useToast } from "@/components/ui";
import type { CampusEvent, EventPayload, EventStatus } from "@/types/event";

interface EventDetailsTabProps {
  event: CampusEvent;
  canEdit: boolean;
  onSave: (payload: EventPayload) => Promise<void>;
}

function toLocalInput(iso: string | null): string {
  return iso ? iso.slice(0, 16) : "";
}

export function EventDetailsTab({ event, canEdit, onSave }: EventDetailsTabProps) {
  const { showToast } = useToast();
  const [form, setForm] = useState<EventPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    setForm({
      title: event.title,
      slug: event.slug,
      venue: event.venue ?? "",
      is_online: event.is_online,
      starts_at: event.starts_at,
      ends_at: event.ends_at,
      description: event.description,
      registration_url: event.registration_url ?? "",
      status: event.status,
      published_at: event.published_at,
    });
  }, [event]);

  async function handleSave() {
    setIsSaving(true);
    try {
      await onSave(form);
      showToast("Event saved.", "success");
    } catch {
      showToast("Could not save. Check the title/slug and dates.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <fieldset disabled={!canEdit} className="flex flex-col gap-4">
      <Input label="Title" value={form.title ?? ""} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
      <Input
        label="Slug"
        hint="Auto-suggested from the title if left blank. Public URL: /events/{slug}."
        value={form.slug ?? ""}
        onChange={(e) => setForm({ ...form, slug: e.target.value })}
      />

      <div className="grid grid-cols-2 gap-3">
        <Input
          label="Venue"
          hint="Leave blank for online-only events."
          value={form.venue ?? ""}
          onChange={(e) => setForm({ ...form, venue: e.target.value })}
        />
        <div className="flex items-end pb-2">
          <Switch label="Online Event" checked={form.is_online ?? false} onChange={(checked) => setForm({ ...form, is_online: checked })} />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <Input
          label="Starts At"
          type="datetime-local"
          value={toLocalInput(form.starts_at ?? null)}
          onChange={(e) => setForm({ ...form, starts_at: e.target.value ? new Date(e.target.value).toISOString() : undefined })}
          required
        />
        <Input
          label="Ends At"
          type="datetime-local"
          hint="Optional."
          value={toLocalInput(form.ends_at ?? null)}
          onChange={(e) => setForm({ ...form, ends_at: e.target.value ? new Date(e.target.value).toISOString() : null })}
        />
      </div>

      <Textarea
        label="Description"
        value={form.description ?? ""}
        onChange={(e) => setForm({ ...form, description: e.target.value })}
        rows={8}
      />

      <Input
        label="Registration URL"
        hint="External or internal registration/inquiry link."
        value={form.registration_url ?? ""}
        onChange={(e) => setForm({ ...form, registration_url: e.target.value })}
      />

      <div className="grid grid-cols-2 gap-3">
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Status</span>
          <select
            value={form.status ?? "draft"}
            onChange={(e) => setForm({ ...form, status: e.target.value as EventStatus })}
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
          hint="Required for Scheduled — the event listing goes live automatically at this time."
          value={toLocalInput(form.published_at ?? null)}
          onChange={(e) => setForm({ ...form, published_at: e.target.value ? new Date(e.target.value).toISOString() : null })}
        />
      </div>

      {canEdit && (
        <Button onClick={handleSave} isLoading={isSaving} className="self-start">
          Save Event
        </Button>
      )}
    </fieldset>
  );
}
