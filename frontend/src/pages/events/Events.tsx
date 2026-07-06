import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Plus, Lock, Trash2 } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Button, Table, Badge, useToast, type TableColumn } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { Countdown } from "@/components/events/Countdown";
import { usePermission } from "@/hooks/usePermission";
import type { ApiCollection, ApiResponse } from "@/types/api";
import type { CampusEvent, EventStatus } from "@/types/event";

const STATUS_TONE: Record<EventStatus, BadgeTone> = {
  draft: "neutral",
  published: "success",
  scheduled: "warning",
  archived: "neutral",
};

/**
 * Events Management — SRS Permission Matrix, "Events" row: Super
 * Admin/Administrator = Full; Content Editor/Marketing = Create/Edit;
 * Admissions = no access. No Publish button — Events has no separate
 * publish action (see EventPolicy's docblock); status is set from the
 * editor's Details tab directly.
 */
export function Events() {
  const navigate = useNavigate();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canCreate = can("events.create");
  const canDelete = can("events.delete");

  const [events, setEvents] = useState<CampusEvent[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreating, setIsCreating] = useState(false);

  const fetchEvents = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiCollection<CampusEvent>>(ENDPOINTS.events.admin(), { params: { per_page: 100 } });
      setEvents(data.data);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!can("events.view")) return;
    fetchEvents();
  }, [fetchEvents, can]);

  async function handleCreate() {
    setIsCreating(true);
    try {
      const { data } = await api.post<ApiResponse<CampusEvent>>(ENDPOINTS.events.admin(), {
        title: "New Event",
        starts_at: new Date(Date.now() + 7 * 86_400_000).toISOString(),
        description: "<p></p>",
      });
      navigate(`/admin/events/${data.data.id}`);
    } catch {
      showToast("Could not create a new event.", "error");
    } finally {
      setIsCreating(false);
    }
  }

  async function handleDelete(event: CampusEvent) {
    await api.delete(ENDPOINTS.events.admin(event.id));
    await fetchEvents();
  }

  if (!can("events.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Events" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Events Management.
          </div>
        </Card>
      </div>
    );
  }

  const columns: TableColumn<CampusEvent>[] = [
    {
      key: "image",
      header: "",
      render: (e) =>
        e.featured_image_url ? (
          <img src={e.featured_image_url} alt="" className="h-10 w-16 rounded-sm object-cover" />
        ) : (
          <div className="h-10 w-16 rounded-sm bg-[color:var(--color-surface-alt)]" />
        ),
    },
    { key: "title", header: "Title", render: (e) => e.title },
    { key: "location", header: "Location", render: (e) => (e.is_online ? "Online" : e.venue ?? "—") },
    { key: "starts_at", header: "Starts", render: (e) => new Date(e.starts_at).toLocaleString() },
    {
      key: "countdown",
      header: "",
      render: (e) => (e.is_upcoming ? <Countdown target={e.starts_at} /> : <span className="text-caption text-neutral-400">Past</span>),
    },
    { key: "status", header: "Status", render: (e) => <Badge tone={STATUS_TONE[e.status]}>{e.status}</Badge> },
    {
      key: "actions",
      header: "",
      render: (e) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => navigate(`/admin/events/${e.id}`)} className="text-body-sm text-navy hover:underline">
            Edit
          </button>
          {canDelete && (
            <button type="button" onClick={() => handleDelete(e)} aria-label={`Delete ${e.title}`}>
              <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" aria-hidden="true" />
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Events" }]} />

      <div className="flex items-center justify-between">
        <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">Events</h1>
        {canCreate && (
          <Button onClick={handleCreate} isLoading={isCreating}>
            <Plus className="h-4 w-4" aria-hidden="true" />
            New Event
          </Button>
        )}
      </div>

      <Card>
        <Table
          columns={columns}
          rows={events}
          rowKey={(e) => e.id}
          isLoading={isLoading}
          emptyTitle="No events yet"
          emptyDescription="Add your first campus event."
        />
      </Card>
    </div>
  );
}
