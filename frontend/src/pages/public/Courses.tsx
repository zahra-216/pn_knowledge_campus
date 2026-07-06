import { useState } from "react";
import { useSearchParams } from "react-router-dom";
import { Search } from "lucide-react";
import { usePublicList } from "@/hooks/usePublicList";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { ContentCard } from "@/components/public/ContentCard";
import { Pagination } from "@/components/public/Pagination";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { Course, CourseLookup } from "@/types/course";
import type { Faculty } from "@/types/faculty";

export function Courses() {
  const [params, setParams] = useSearchParams();
  const [searchInput, setSearchInput] = useState(params.get("search") ?? "");
  const page = Number(params.get("page") ?? 1);

  const { data: faculties } = usePublicDetail<Faculty[]>(ENDPOINTS.faculties.publicList);
  const { data: levels } = usePublicDetail<CourseLookup[]>(ENDPOINTS.courseLevels.public);
  const { data: modes } = usePublicDetail<CourseLookup[]>(ENDPOINTS.courseModes.public);

  const { items: courses, meta, isLoading, error } = usePublicList<Course>(ENDPOINTS.courses.publicList, {
    page,
    per_page: 12,
    "filter[faculty]": params.get("faculty") ?? undefined,
    "filter[level]": params.get("level") ?? undefined,
    "filter[mode]": params.get("mode") ?? undefined,
    search: params.get("search") ?? undefined,
  });

  useSeoHead({ title: "Courses", canonicalPath: "/courses" });

  function updateFilter(key: string, value: string) {
    const next = new URLSearchParams(params);
    if (value) next.set(key, value);
    else next.delete(key);
    next.delete("page");
    setParams(next);
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Courses" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Courses</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          updateFilter("search", searchInput);
        }}
        className="mt-6 flex flex-wrap gap-3"
      >
        <div className="relative flex-1 min-w-[220px]">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
          <input
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            placeholder="Search courses..."
            className="h-10 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
          />
        </div>

        <FilterSelect
          value={params.get("faculty") ?? ""}
          onChange={(v) => updateFilter("faculty", v)}
          placeholder="All Faculties"
          options={(faculties ?? []).map((f) => ({ value: f.slug, label: f.name }))}
        />
        <FilterSelect
          value={params.get("level") ?? ""}
          onChange={(v) => updateFilter("level", v)}
          placeholder="All Levels"
          options={(levels ?? []).map((l) => ({ value: l.slug, label: l.name }))}
        />
        <FilterSelect
          value={params.get("mode") ?? ""}
          onChange={(v) => updateFilter("mode", v)}
          placeholder="All Modes"
          options={(modes ?? []).map((m) => ({ value: m.slug, label: m.name }))}
        />
      </form>

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={courses.length === 0}
          emptyState={<EmptyState title="No courses match your filters" description="Try clearing a filter or searching a different term." />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {courses.map((c) => (
              <ContentCard
                key={c.id}
                to={`/courses/${c.slug}`}
                image={c.featured_image_url}
                title={c.course_name}
                meta={c.faculty.name}
                excerpt={c.overview}
                badge={c.duration}
              />
            ))}
          </div>
          {meta && (
            <Pagination
              meta={meta}
              onPageChange={(p) => {
                const next = new URLSearchParams(params);
                next.set("page", String(p));
                setParams(next);
              }}
            />
          )}
        </AsyncState>
      </div>
    </div>
  );
}

function FilterSelect({
  value,
  onChange,
  placeholder,
  options,
}: {
  value: string;
  onChange: (value: string) => void;
  placeholder: string;
  options: { value: string; label: string }[];
}) {
  return (
    <select
      value={value}
      onChange={(e) => onChange(e.target.value)}
      className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body-sm"
    >
      <option value="">{placeholder}</option>
      {options.map((o) => (
        <option key={o.value} value={o.value}>
          {o.label}
        </option>
      ))}
    </select>
  );
}
