import { useParams } from "react-router-dom";
import { Calendar, MapPin, Video, Ticket } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Countdown } from "@/components/public/Countdown";
import { NotFound } from "@/pages/public/NotFound";
import { Card } from "@/components/ui";
import type { CampusEvent } from "@/types/event";

export function EventDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: event, isLoading, error } = usePublicDetail<CampusEvent>(ENDPOINTS.events.public(slug));

  useSeoHead({
    title: event?.title ?? "Event",
    description: event?.description,
    canonicalPath: `/events/${slug}`,
    imageUrl: event?.featured_image_url,
    type: "article",
    seo: event?.seo,
    jsonLd: event
      ? {
          "@context": "https://schema.org",
          "@type": "Event",
          name: event.title,
          startDate: event.starts_at,
          endDate: event.ends_at,
          eventAttendanceMode: event.is_online
            ? "https://schema.org/OnlineEventAttendanceMode"
            : "https://schema.org/OfflineEventAttendanceMode",
          location: event.venue ? { "@type": "Place", name: event.venue } : undefined,
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {event && (
        <div>
          <div className="relative overflow-hidden bg-navy text-white">
            {event.featured_image_url && (
              <img src={event.featured_image_url} alt="" className="absolute inset-0 h-full w-full object-cover opacity-30" />
            )}
            <div className="relative mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
              <Breadcrumb items={[{ label: "Events", to: "/events" }, { label: event.title }]} />
              <h1 className="mt-4 font-display text-h1 font-semibold">{event.title}</h1>
              <div className="mt-4 flex flex-wrap gap-4 text-body-sm text-white/85">
                <span className="flex items-center gap-1.5">
                  <Calendar className="h-4 w-4" />
                  {new Date(event.starts_at).toLocaleString(undefined, { dateStyle: "long", timeStyle: "short" })}
                </span>
                {event.is_online ? (
                  <span className="flex items-center gap-1.5">
                    <Video className="h-4 w-4" /> Online Event
                  </span>
                ) : (
                  event.venue && (
                    <span className="flex items-center gap-1.5">
                      <MapPin className="h-4 w-4" /> {event.venue}
                    </span>
                  )
                )}
              </div>

              {event.is_upcoming && (
                <div className="mt-6">
                  <Countdown startsAt={event.starts_at} />
                </div>
              )}
            </div>
          </div>

          <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <p className="whitespace-pre-line text-body text-[color:var(--color-text)]">{event.description}</p>

            {event.is_upcoming && event.registration_url && (
              <a
                href={event.registration_url}
                target="_blank"
                rel="noopener noreferrer"
                className="mt-6 inline-flex h-11 items-center gap-2 rounded bg-navy px-6 text-body-sm font-semibold text-white hover:bg-navy-light"
              >
                <Ticket className="h-4 w-4" /> Register Now
              </a>
            )}

            {event.speakers.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Speakers</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  {event.speakers.map((s) => (
                    <Card key={s.id} className="flex items-center gap-3">
                      {s.photo_url && <img src={s.photo_url} alt={s.name} loading="lazy" decoding="async" className="h-14 w-14 rounded-full object-cover" />}
                      <div>
                        <p className="font-semibold text-[color:var(--color-text)]">{s.name}</p>
                        {s.title && <p className="text-body-sm text-neutral-500">{s.title}</p>}
                      </div>
                    </Card>
                  ))}
                </div>
              </section>
            )}

            {event.gallery.length > 0 && (
              <section className="mt-10">
                <h2 className="font-display text-h3 font-semibold text-[color:var(--color-text)]">Gallery</h2>
                <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                  {event.gallery.map((g) => (
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
