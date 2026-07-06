import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { ContentCard } from "@/components/public/ContentCard";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { Faculty } from "@/types/faculty";

export function Faculties() {
  const { data: faculties, isLoading, error } = usePublicDetail<Faculty[]>(ENDPOINTS.faculties.publicList);

  useSeoHead({ title: "Faculties", canonicalPath: "/faculties" });

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Faculties" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Faculties</h1>

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={(faculties ?? []).length === 0}
          emptyState={<EmptyState title="No faculties published yet" />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {(faculties ?? []).map((f) => (
              <ContentCard key={f.id} to={`/faculties/${f.slug}`} image={f.banner_url ?? f.icon_url} title={f.name} excerpt={f.short_description} />
            ))}
          </div>
        </AsyncState>
      </div>
    </div>
  );
}
