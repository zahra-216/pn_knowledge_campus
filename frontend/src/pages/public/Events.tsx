import { useState } from "react";
import { useSearchParams } from "react-router-dom";
import { Search } from "lucide-react";
import { usePublicList } from "@/hooks/usePublicList";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { ContentCard } from "@/components/public/ContentCard";
import { Pagination } from "@/components/public/Pagination";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { CampusEvent } from "@/types/event";

export function Events() {
  const [params, setParams] = useSearchParams();
  const [searchInput, setSearchInput] = useState(params.get("search") ?? "");
  const showPast = params.get("tab") === "past";
  const page = Number(params.get("page") ?? 1);

  const { items: events, meta, isLoading, error } = usePublicList<CampusEvent>(ENDPOINTS.events.publicList, {
    page,
    per_page: 9,
    "filter[past]": showPast ? 1 : undefined,
    search: params.get("search") ?? undefined,
  });

  useSeoHead({ title: "Events", canonicalPath: "/events" });

  function setTab(tab: "upcoming" | "past") {
    const next = new URLSearchParams(params);
    if (tab === "past") next.set("tab", "past");
    else next.delete("tab");
    next.delete("page");
    setParams(next);
  }

  function updateSearch(value: string) {
    const next = new URLSearchParams(params);
    if (value) next.set("search", value);
    else next.delete("search");
    next.delete("page");
    setParams(next);
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Events" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Events</h1>

      <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div className="flex gap-2">
          <FilterChip label="Upcoming" active={!showPast} onClick={() => setTab("upcoming")} />
          <FilterChip label="Past" active={showPast} onClick={() => setTab("past")} />
        </div>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            updateSearch(searchInput);
          }}
          className="relative"
        >
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
          <input
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            placeholder="Search events..."
            className="h-10 w-64 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
          />
        </form>
      </div>

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={events.length === 0}
          emptyState={<EmptyState title={showPast ? "No past events yet" : "No upcoming events scheduled"} />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {events.map((ev) => (
              <ContentCard
                key={ev.id}
                to={`/events/${ev.slug}`}
                image={ev.featured_image_url}
                title={ev.title}
                meta={new Date(ev.starts_at).toLocaleDateString(undefined, { day: "numeric", month: "long", year: "numeric" })}
                excerpt={ev.venue ?? (ev.is_online ? "Online Event" : null)}
              />
            ))}
          </div>
          {meta && (
            <Pagination
              meta={meta}
              onPageChange={(pg) => {
                const next = new URLSearchParams(params);
                next.set("page", String(pg));
                setParams(next);
              }}
            />
          )}
        </AsyncState>
      </div>
    </div>
  );
}

function FilterChip({ label, active, onClick }: { label: string; active: boolean; onClick: () => void }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`rounded-full border px-4 py-1.5 text-body-sm font-medium transition-colors ${
        active ? "border-navy bg-navy text-white" : "border-[color:var(--color-border)] text-neutral-600 hover:bg-navy/5"
      }`}
    >
      {label}
    </button>
  );
}
