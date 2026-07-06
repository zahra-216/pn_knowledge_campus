import { Link, useParams } from "react-router-dom";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { NotFound } from "@/pages/public/NotFound";
import { Card } from "@/components/ui";
import type { Department } from "@/types/department";

export function DepartmentDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: department, isLoading, error } = usePublicDetail<Department>(ENDPOINTS.departments.public(slug));

  useSeoHead({
    title: department?.name ?? "Department",
    description: department?.short_description,
    canonicalPath: `/departments/${slug}`,
    imageUrl: department?.banner_url,
    seo: department?.seo,
    jsonLd: department
      ? {
          "@context": "https://schema.org",
          "@type": "EducationalOrganization",
          name: department.name,
          description: department.short_description ?? department.description ?? undefined,
          image: department.banner_url ?? undefined,
          parentOrganization: { "@type": "EducationalOrganization", name: department.faculty.name },
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {department && (
        <div>
          {department.banner_url && (
            <div className="relative h-64 w-full overflow-hidden bg-navy sm:h-80">
              <img src={department.banner_url} alt="" className="h-full w-full object-cover opacity-70" />
            </div>
          )}

          <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <Breadcrumb
              items={[
                { label: "Faculties", to: "/faculties" },
                { label: department.faculty.name, to: `/faculties/${department.faculty.slug}` },
                { label: department.name },
              ]}
            />
            <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">{department.name}</h1>
            {department.short_description && <p className="mt-2 text-body-lg text-neutral-500">{department.short_description}</p>}
            {department.description && (
              <p className="mt-6 whitespace-pre-line text-body text-[color:var(--color-text)]">{department.description}</p>
            )}

            {department.courses.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Courses</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                  {department.courses.map((c) => (
                    <Link key={c.id} to={`/courses/${c.slug}`}>
                      <Card className="transition-shadow hover:shadow-2">
                        <p className="font-semibold text-[color:var(--color-text)]">{c.course_name}</p>
                        <p className="mt-1 line-clamp-2 text-body-sm text-neutral-500">{c.overview}</p>
                      </Card>
                    </Link>
                  ))}
                </div>
              </section>
            )}
          </div>
        </div>
      )}
    </AsyncState>
  );
}
