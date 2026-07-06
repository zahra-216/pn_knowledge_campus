import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Tabs, Spinner, Badge, type TabItem } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import { SeoFieldsPanel } from "@/components/seo/SeoFieldsPanel";
import { Countdown } from "@/components/events/Countdown";
import { EventDetailsTab } from "./components/EventDetailsTab";
import { EventSpeakersTab } from "./components/EventSpeakersTab";
import { EventMediaTab } from "./components/EventMediaTab";
import type { ApiResponse } from "@/types/api";
import type { CampusEvent, EventPayload, EventStatus } from "@/types/event";

const TABS: TabItem[] = [
  { key: "details", label: "Details" },
  { key: "speakers", label: "Speakers" },
  { key: "media", label: "Media" },
  { key: "seo", label: "SEO" },
];

const STATUS_TONE: Record<EventStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

export function EventEditor() {
  const { id } = useParams<{ id: string }>();
  const eventId = Number(id);
  const { can } = usePermission();
  const canEdit = can("events.edit");

  const [activeTab, setActiveTab] = useState("details");
  const [event, setEvent] = useState<CampusEvent | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchEvent = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<CampusEvent>>(ENDPOINTS.events.admin(eventId));
      setEvent(data.data);
    } finally {
      setIsLoading(false);
    }
  }, [eventId]);

  useEffect(() => {
    if (!can("events.view")) return;
    fetchEvent();
  }, [fetchEvent, can]);

  async function handleSave(payload: EventPayload) {
    const { data } = await api.put<ApiResponse<CampusEvent>>(ENDPOINTS.events.admin(eventId), payload);
    setEvent(data.data);
  }

  if (!can("events.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Events", to: "/admin/events" }, { label: "Edit" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Events Management.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !event) {
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Events", to: "/admin/events" }, { label: event.title }]} />

      <div className="flex items-center gap-3">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">{event.title}</h1>
        <Badge tone={STATUS_TONE[event.status]}>{event.status}</Badge>
        {event.is_upcoming && <Countdown target={event.starts_at} />}
      </div>

      <Card>
        <Tabs items={TABS} active={activeTab} onChange={setActiveTab}>
          {activeTab === "details" && <EventDetailsTab event={event} canEdit={canEdit} onSave={handleSave} />}
          {activeTab === "speakers" && <EventSpeakersTab eventId={event.id} canEdit={canEdit} />}
          {activeTab === "media" && <EventMediaTab event={event} canEdit={canEdit} onSave={handleSave} onRefresh={fetchEvent} />}
          {activeTab === "seo" && <SeoFieldsPanel type="event" id={event.id} canEdit={canEdit} />}
        </Tabs>
      </Card>
    </div>
  );
}
