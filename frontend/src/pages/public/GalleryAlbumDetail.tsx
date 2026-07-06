import { useState } from "react";
import { useParams } from "react-router-dom";
import { PlayCircle } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { MasonryGrid, MasonryItem } from "@/components/gallery/MasonryGrid";
import { NotFound } from "@/pages/public/NotFound";
import { Modal } from "@/components/ui";
import type { GalleryAlbum, GalleryItem, GalleryItemType } from "@/types/gallery";

type Filter = "all" | GalleryItemType;

export function GalleryAlbumDetail() {
  const { slug = "" } = useParams<{ slug: string }>();
  const { data: album, isLoading, error } = usePublicDetail<GalleryAlbum>(ENDPOINTS.galleryAlbums.public(slug));
  const [filter, setFilter] = useState<Filter>("all");
  const [active, setActive] = useState<GalleryItem | null>(null);

  useSeoHead({
    title: album?.title ?? "Gallery Album",
    description: album?.description,
    canonicalPath: `/gallery/${slug}`,
    imageUrl: album?.cover_url,
    jsonLd: album
      ? {
          "@context": "https://schema.org",
          "@type": "CollectionPage",
          name: album.title,
          description: album.description ?? undefined,
          image: album.cover_url ?? undefined,
        }
      : null,
  });

  if (error?.status === 404) return <NotFound />;

  const items = (album?.items ?? []).filter((item) => filter === "all" || item.type === filter);

  return (
    <AsyncState isLoading={isLoading} error={error && error.status !== 404 ? error : null}>
      {album && (
        <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
          <Breadcrumb items={[{ label: "Gallery", to: "/gallery" }, { label: album.title }]} />
          <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">{album.title}</h1>
          {album.description && <p className="mt-2 max-w-2xl text-body text-neutral-500">{album.description}</p>}

          <div className="mt-6 flex gap-2">
            <FilterChip label="All" active={filter === "all"} onClick={() => setFilter("all")} />
            <FilterChip label="Photos" active={filter === "image"} onClick={() => setFilter("image")} />
            <FilterChip label="Videos" active={filter === "video"} onClick={() => setFilter("video")} />
          </div>

          <div className="mt-8">
            <MasonryGrid>
              {items.map((item) => (
                <MasonryItem key={item.id}>
                  <button type="button" onClick={() => setActive(item)} className="group relative block w-full">
                    <img src={item.thumb_url ?? item.url} alt={item.caption ?? ""} loading="lazy" decoding="async" className="w-full rounded-lg object-cover" />
                    {item.type === "video" && (
                      <span className="absolute inset-0 flex items-center justify-center rounded-lg bg-black/20 group-hover:bg-black/30">
                        <PlayCircle className="h-10 w-10 text-white" />
                      </span>
                    )}
                    {item.caption && (
                      <span className="mt-1 block truncate text-caption text-neutral-500">{item.caption}</span>
                    )}
                  </button>
                </MasonryItem>
              ))}
            </MasonryGrid>
          </div>

          <Modal open={!!active} onClose={() => setActive(null)} title={active?.caption ?? album.title} size="wide">
            {active?.type === "video" ? (
              <video src={active.url} controls className="max-h-[70vh] w-full rounded-lg" />
            ) : (
              active && <img src={active.url} alt={active.caption ?? ""} className="max-h-[70vh] w-full rounded-lg object-contain" />
            )}
          </Modal>
        </div>
      )}
    </AsyncState>
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
