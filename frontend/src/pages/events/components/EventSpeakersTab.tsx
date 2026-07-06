import { useCallback, useEffect, useState } from "react";
import { Plus, Pencil, Trash2 } from "lucide-react";
import { Button, Modal, Input, Textarea, useToast } from "@/components/ui";
import { MediaIdField } from "@/components/content-blocks/MediaIdField";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import type { ApiResponse } from "@/types/api";
import type { EventSpeaker, EventSpeakerPayload } from "@/types/event";

interface EventSpeakersTabProps {
  eventId: number;
  canEdit: boolean;
}

/** Not in the Database Design document — a client-requested Events feature (see event_speakers migration's docblock). */
export function EventSpeakersTab({ eventId, canEdit }: EventSpeakersTabProps) {
  const { showToast } = useToast();
  const [speakers, setSpeakers] = useState<EventSpeaker[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [formState, setFormState] = useState<{ open: boolean; speaker: EventSpeaker | null }>({ open: false, speaker: null });
  const [form, setForm] = useState<EventSpeakerPayload>({});
  const [isSaving, setIsSaving] = useState(false);

  const fetchSpeakers = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<EventSpeaker[]>>(ENDPOINTS.events.speakers(eventId));
      setSpeakers(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [eventId]);

  useEffect(() => {
    fetchSpeakers();
  }, [fetchSpeakers]);

  function openForm(speaker: EventSpeaker | null) {
    setFormState({ open: true, speaker });
    setForm({
      name: speaker?.name ?? "",
      title: speaker?.title ?? "",
      bio: speaker?.bio ?? "",
      order: speaker?.order ?? 0,
    });
  }

  async function handleSave() {
    setIsSaving(true);
    try {
      if (formState.speaker) {
        await api.put(ENDPOINTS.events.speakers(eventId, formState.speaker.id), form);
      } else {
        await api.post(ENDPOINTS.events.speakers(eventId), form);
      }
      setFormState({ open: false, speaker: null });
      await fetchSpeakers();
    } catch {
      showToast("Could not save this speaker.", "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleDelete(speaker: EventSpeaker) {
    await api.delete(ENDPOINTS.events.speakers(eventId, speaker.id));
    await fetchSpeakers();
  }

  if (isLoading) {
    return <p className="text-body-sm text-neutral-500">Loading...</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {speakers.map((speaker) => (
        <div key={speaker.id} className="flex items-start gap-3 rounded-md border border-[color:var(--color-border)] p-3">
          {speaker.photo_url ? (
            <img src={speaker.photo_url} alt="" className="h-14 w-14 flex-shrink-0 rounded-full object-cover" />
          ) : (
            <div className="h-14 w-14 flex-shrink-0 rounded-full bg-[color:var(--color-surface-alt)]" />
          )}
          <div className="flex flex-1 flex-col gap-1">
            <span className="text-body-sm font-semibold text-[color:var(--color-text)]">{speaker.name}</span>
            {speaker.title && <span className="text-caption text-neutral-500">{speaker.title}</span>}
            {speaker.bio && <p className="text-body-sm text-neutral-500">{speaker.bio}</p>}
          </div>
          {canEdit && (
            <div className="flex flex-shrink-0 items-center gap-1">
              <button type="button" onClick={() => openForm(speaker)} aria-label="Edit speaker" className="rounded p-1.5 text-neutral-500 hover:bg-black/5">
                <Pencil className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
              <button type="button" onClick={() => handleDelete(speaker)} aria-label="Delete speaker" className="rounded p-1.5 text-neutral-500 hover:text-danger">
                <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
            </div>
          )}
        </div>
      ))}

      {speakers.length === 0 && <p className="text-body-sm text-neutral-500">No speakers added yet.</p>}

      {canEdit && (
        <Button variant="secondary" onClick={() => openForm(null)} className="self-start">
          <Plus className="h-4 w-4" aria-hidden="true" />
          Add Speaker
        </Button>
      )}

      <Modal
        open={formState.open}
        onClose={() => setFormState({ open: false, speaker: null })}
        title={formState.speaker ? "Edit Speaker" : "New Speaker"}
        footer={
          <>
            <Button variant="secondary" onClick={() => setFormState({ open: false, speaker: null })}>
              Cancel
            </Button>
            <Button onClick={handleSave} isLoading={isSaving}>
              Save
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-4">
          <Input label="Name" value={form.name ?? ""} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
          <Input
            label="Title / Role"
            hint="e.g. Dean of Admissions"
            value={form.title ?? ""}
            onChange={(e) => setForm({ ...form, title: e.target.value })}
          />
          <Textarea label="Bio" value={form.bio ?? ""} onChange={(e) => setForm({ ...form, bio: e.target.value })} rows={3} />
          <MediaIdField
            label="Photo"
            type="image"
            mediaId={null}
            previewUrl={formState.speaker?.photo_url ?? null}
            onChange={(id) => setForm({ ...form, photo_media_id: id })}
          />
        </div>
      </Modal>
    </div>
  );
}
