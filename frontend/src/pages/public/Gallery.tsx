import { useSearchParams } from "react-router-dom";
import { Link } from "react-router-dom";
import { ImageOff, Images } from "lucide-react";
import { usePublicList } from "@/hooks/usePublicList";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Card, EmptyState, Pagination } from "@/components/ui";
import type { GalleryAlbum } from "@/types/gallery";

export function Gallery() {
  const [params, setParams] = useSearchParams();
  const page = Number(params.get("page") ?? 1);

  const { items: albums, meta, isLoading, error } = usePublicList<GalleryAlbum>(ENDPOINTS.galleryAlbums.publicList, {
    page,
    per_page: 12,
  });

  useSeoHead({ title: "Gallery", canonicalPath: "/gallery" });

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Gallery" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Gallery</h1>

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={albums.length === 0}
          emptyState={<EmptyState icon={Images} title="No albums published yet" />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {albums.map((album) => (
              <Link key={album.id} to={`/gallery/${album.slug}`}>
                <Card className="flex h-full flex-col overflow-hidden p-0 transition-shadow hover:shadow-2">
                  <div className="relative aspect-[4/3] w-full bg-neutral-100 dark:bg-white/5">
                    {album.cover_url ? (
                      <img src={album.cover_url} alt={album.title} loading="lazy" decoding="async" className="h-full w-full object-cover" />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-neutral-400">
                        <ImageOff className="h-8 w-8" />
                      </div>
                    )}
                  </div>
                  <div className="p-5">
                    <h3 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{album.title}</h3>
                    {album.items_count !== undefined && (
                      <p className="mt-1 text-body-sm text-neutral-500">{album.items_count} items</p>
                    )}
                  </div>
                </Card>
              </Link>
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
