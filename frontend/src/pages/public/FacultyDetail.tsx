import { Link, useParams } from "react-router-dom";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { NotFound } from "@/pages/public/NotFound";
import { Card } from "@/components/ui";
import type { Faculty } from "@/types/faculty";

export function FacultyDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: faculty, isLoading, error } = usePublicDetail<Faculty>(ENDPOINTS.faculties.public(slug));

  useSeoHead({
    title: faculty?.name ?? "Faculty",
    description: faculty?.short_description,
    canonicalPath: `/faculties/${slug}`,
    imageUrl: faculty?.banner_url,
    seo: faculty?.seo,
    jsonLd: faculty
      ? {
          "@context": "https://schema.org",
          "@type": "EducationalOrganization",
          name: faculty.name,
          description: faculty.short_description ?? faculty.description ?? undefined,
          image: faculty.banner_url ?? undefined,
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {faculty && (
        <div>
          {faculty.banner_url && (
            <div className="relative h-64 w-full overflow-hidden bg-navy sm:h-80">
              <img src={faculty.banner_url} alt="" className="h-full w-full object-cover opacity-70" />
            </div>
          )}

          <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <Breadcrumb items={[{ label: "Faculties", to: "/faculties" }, { label: faculty.name }]} />
            <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">{faculty.name}</h1>
            {faculty.short_description && <p className="mt-2 text-body-lg text-neutral-500">{faculty.short_description}</p>}
            {faculty.description && <p className="mt-6 whitespace-pre-line text-body text-[color:var(--color-text)]">{faculty.description}</p>}

            {faculty.dean_name && (
              <Card className="mt-8 flex items-center gap-4">
                {faculty.dean_photo_url && (
                  <img src={faculty.dean_photo_url} alt={faculty.dean_name} loading="lazy" decoding="async" className="h-16 w-16 rounded-full object-cover" />
                )}
                <div>
                  <p className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{faculty.dean_name}</p>
                  {faculty.dean_title && <p className="text-body-sm text-neutral-500">{faculty.dean_title}</p>}
                  {faculty.dean_message && <p className="mt-2 text-body-sm italic text-neutral-500">&ldquo;{faculty.dean_message}&rdquo;</p>}
                </div>
              </Card>
            )}

            {faculty.departments.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Departments</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                  {faculty.departments.map((d) => (
                    <Link key={d.id} to={`/departments/${d.slug}`}>
                      <Card className="transition-shadow hover:shadow-2">
                        <p className="font-semibold text-[color:var(--color-text)]">{d.name}</p>
                        {d.short_description && <p className="mt-1 text-body-sm text-neutral-500">{d.short_description}</p>}
                      </Card>
                    </Link>
                  ))}
                </div>
              </section>
            )}

            {faculty.courses.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Courses</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                  {faculty.courses.map((c) => (
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

            {faculty.gallery.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Gallery</h2>
                <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                  {faculty.gallery.map((g) => (
                    <img key={g.id} src={g.thumb_url} alt="" loading="lazy" decoding="async" className="aspect-square w-full rounded-lg object-cover" />
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
