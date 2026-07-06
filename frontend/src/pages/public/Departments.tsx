import { useSearchParams } from "react-router-dom";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { ContentCard } from "@/components/public/ContentCard";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { Faculty } from "@/types/faculty";
import type { Department } from "@/types/department";

export function Departments() {
  const [params, setParams] = useSearchParams();
  const facultyFilter = params.get("faculty") ?? "";

  const { data: faculties } = usePublicDetail<Faculty[]>(ENDPOINTS.faculties.publicList);
  const endpoint = facultyFilter
    ? `${ENDPOINTS.departments.publicList}?faculty=${encodeURIComponent(facultyFilter)}`
    : ENDPOINTS.departments.publicList;
  const { data: departments, isLoading, error } = usePublicDetail<Department[]>(endpoint);

  useSeoHead({ title: "Departments", canonicalPath: "/departments" });

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Departments" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Departments</h1>

      {faculties && faculties.length > 0 && (
        <div className="mt-6 flex flex-wrap items-center gap-2">
          <FilterChip label="All" active={!facultyFilter} onClick={() => setParams({})} />
          {faculties.map((f) => (
            <FilterChip key={f.id} label={f.name} active={facultyFilter === f.slug} onClick={() => setParams({ faculty: f.slug })} />
          ))}
        </div>
      )}

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={(departments ?? []).length === 0}
          emptyState={<EmptyState title="No departments published yet" />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {(departments ?? []).map((d) => (
              <ContentCard
                key={d.id}
                to={`/departments/${d.slug}`}
                image={d.banner_url}
                title={d.name}
                meta={d.faculty.name}
                excerpt={d.short_description}
              />
            ))}
          </div>
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
