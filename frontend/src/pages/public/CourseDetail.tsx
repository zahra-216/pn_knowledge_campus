import { useState } from "react";
import { Link, useParams } from "react-router-dom";
import { Download, Clock, Layers, GraduationCap } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Accordion } from "@/components/public/Accordion";
import { EnquireNowModal } from "@/components/public/EnquireNowModal";
import { NotFound } from "@/pages/public/NotFound";
import { Badge } from "@/components/ui";
import type { Course, CourseCurriculumItem } from "@/types/course";

function flattenCurriculum(items: CourseCurriculumItem[], depth = 0): { id: number; question: string; answer: string }[] {
  return items.flatMap((item) => [
    {
      id: item.id,
      question: `${"— ".repeat(depth)}${item.title}${item.duration ? ` (${item.duration})` : ""}`,
      answer: item.description ?? "",
    },
    ...flattenCurriculum(item.children, depth + 1),
  ]);
}

export function CourseDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: course, isLoading, error } = usePublicDetail<Course>(ENDPOINTS.courses.public(slug));
  const [enquireOpen, setEnquireOpen] = useState(false);

  useSeoHead({
    title: course?.course_name ?? "Course",
    description: course?.overview,
    canonicalPath: `/courses/${slug}`,
    imageUrl: course?.featured_image_url,
    type: "article",
    seo: course?.seo,
    jsonLd: course
      ? {
          "@context": "https://schema.org",
          "@type": "Course",
          name: course.course_name,
          description: course.overview,
          provider: { "@type": "Organization", name: "PN Knowledge Campus" },
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {course && (
        <div>
          <div className="relative h-72 w-full overflow-hidden bg-navy sm:h-96">
            {course.featured_image_url && (
              <img src={course.featured_image_url} alt="" className="h-full w-full object-cover opacity-50" />
            )}
            <div className="absolute inset-0 flex items-end bg-gradient-to-t from-navy-dark/90 via-navy/40 to-transparent">
              <div className="mx-auto w-full max-w-5xl px-4 pb-8 sm:px-6 lg:px-8">
                <Breadcrumb
                  items={[
                    { label: "Courses", to: "/courses" },
                    { label: course.faculty.name, to: `/faculties/${course.faculty.slug}` },
                    { label: course.course_name },
                  ]}
                />
                <h1 className="mt-3 font-display text-h1 font-semibold text-white">{course.course_name}</h1>
              </div>
            </div>
          </div>

          <div className="mx-auto grid max-w-5xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div className="lg:col-span-2">
              <div className="flex flex-wrap gap-2">
                <Badge tone="info">
                  <Layers className="mr-1 inline h-3.5 w-3.5" /> {course.level.name}
                </Badge>
                <Badge tone="neutral">
                  <Clock className="mr-1 inline h-3.5 w-3.5" /> {course.duration}
                </Badge>
                <Badge tone="neutral">
                  <GraduationCap className="mr-1 inline h-3.5 w-3.5" /> {course.mode.name}
                </Badge>
              </div>

              <p className="mt-6 text-body-lg text-[color:var(--color-text)]">{course.overview}</p>
              {course.description && <p className="mt-4 whitespace-pre-line text-body text-neutral-500">{course.description}</p>}

              {course.entry_requirements && (
                <Section title="Entry Requirements" body={course.entry_requirements} />
              )}
              {course.learning_outcomes && <Section title="Learning Outcomes" body={course.learning_outcomes} />}
              {course.career_opportunities && <Section title="Career Opportunities" body={course.career_opportunities} />}

              {course.curriculum.length > 0 && (
                <section className="mt-10">
                  <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Curriculum</h2>
                  <div className="mt-4">
                    <Accordion items={flattenCurriculum(course.curriculum)} />
                  </div>
                </section>
              )}

              {course.faqs.filter((f) => f.is_active).length > 0 && (
                <section className="mt-10">
                  <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Frequently Asked Questions</h2>
                  <div className="mt-4">
                    <Accordion items={course.faqs.filter((f) => f.is_active).map((f) => ({ id: f.id, question: f.question, answer: f.answer }))} />
                  </div>
                </section>
              )}

              {course.gallery.length > 0 && (
                <section className="mt-10">
                  <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Gallery</h2>
                  <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    {course.gallery.map((g) => (
                      <img key={g.id} src={g.thumb_url} alt="" loading="lazy" decoding="async" className="aspect-square w-full rounded-lg object-cover" />
                    ))}
                  </div>
                </section>
              )}
            </div>

            <aside className="flex flex-col gap-4">
              <div className="rounded-lg border border-[color:var(--color-border)] p-6">
                {course.price.amount !== null && (
                  <p className="font-display text-h3 font-semibold text-navy dark:text-white">
                    {course.price.currency} {course.price.discount_amount ?? course.price.amount}
                    {course.price.discount_amount && (
                      <span className="ml-2 text-body-sm font-normal text-neutral-400 line-through">
                        {course.price.currency} {course.price.amount}
                      </span>
                    )}
                  </p>
                )}
                <Link
                  to={`/apply?course=${course.slug}`}
                  className="mt-4 flex h-11 w-full items-center justify-center rounded bg-gold text-body-sm font-semibold text-navy-dark hover:bg-gold-tint"
                >
                  Apply Now
                </Link>
                <button
                  type="button"
                  onClick={() => setEnquireOpen(true)}
                  className="mt-3 h-11 w-full rounded border border-navy text-body-sm font-semibold text-navy hover:bg-navy/5 dark:border-white dark:text-white dark:hover:bg-white/10"
                >
                  Enquire Now
                </button>

                {course.downloads.length > 0 && (
                  <div className="mt-6 flex flex-col gap-2 border-t border-[color:var(--color-border)] pt-4">
                    <p className="text-body-sm font-semibold text-[color:var(--color-text)]">Downloads</p>
                    {course.downloads.map((d) => (
                      <a
                        key={d.id}
                        href={d.url}
                        download
                        className="flex items-center gap-2 text-body-sm text-navy hover:underline dark:text-gold"
                      >
                        <Download className="h-4 w-4" /> {d.name}
                      </a>
                    ))}
                  </div>
                )}
              </div>
            </aside>
          </div>

          <EnquireNowModal
            open={enquireOpen}
            onClose={() => setEnquireOpen(false)}
            courseId={course.id}
            courseName={course.course_name}
          />
        </div>
      )}
    </AsyncState>
  );
}

function Section({ title, body }: { title: string; body: string }) {
  return (
    <section className="mt-8">
      <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{title}</h2>
      <p className="mt-2 whitespace-pre-line text-body-sm text-neutral-500">{body}</p>
    </section>
  );
}
