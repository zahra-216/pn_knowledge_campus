import { useState } from "react";
import { Download as DownloadIcon, FileText, Lock } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { RequestDownloadModal } from "@/components/public/RequestDownloadModal";
import { Card, EmptyState } from "@/components/ui";
import type { Download, DownloadCategory } from "@/types/download";

function formatSize(bytes: number | null): string | null {
  if (!bytes) return null;
  const kb = bytes / 1024;
  if (kb < 1024) return `${kb.toFixed(0)} KB`;
  return `${(kb / 1024).toFixed(1)} MB`;
}

/** The Downloads catalog (Milestone 18) — Prospectus, Application Forms, Brochures, and other public documents. */
export function Downloads() {
  const [categorySlug, setCategorySlug] = useState("");
  const [requestTarget, setRequestTarget] = useState<Download | null>(null);

  const { data: categories } = usePublicDetail<DownloadCategory[]>(ENDPOINTS.downloadCategories.public);

  const endpoint = categorySlug ? `${ENDPOINTS.downloads.publicList}?category=${categorySlug}` : ENDPOINTS.downloads.publicList;
  const { data: downloads, isLoading, error } = usePublicDetail<Download[]>(endpoint);

  useSeoHead({ title: "Downloads", canonicalPath: "/downloads" });

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Downloads" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Downloads</h1>
      <p className="mt-2 text-body text-neutral-500">Prospectus, application forms, brochures, and other documents.</p>

      {categories && categories.length > 0 && (
        <div className="mt-6 flex flex-wrap gap-2">
          <FilterChip label="All" active={!categorySlug} onClick={() => setCategorySlug("")} />
          {categories.map((c) => (
            <FilterChip key={c.id} label={c.name} active={categorySlug === c.slug} onClick={() => setCategorySlug(c.slug)} />
          ))}
        </div>
      )}

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={(downloads ?? []).length === 0}
          emptyState={<EmptyState icon={FileText} title="No downloads available" description="Check back later or try a different category." />}
        >
          <div className="flex flex-col gap-3">
            {(downloads ?? []).map((d) => (
              <Card key={d.id} className="flex items-center gap-4">
                <FileText className="h-9 w-9 flex-shrink-0 text-navy dark:text-gold" aria-hidden="true" />
                <div className="flex-1">
                  <p className="font-semibold text-[color:var(--color-text)]">{d.title}</p>
                  {d.description && <p className="mt-1 text-body-sm text-neutral-500">{d.description}</p>}
                  {formatSize(d.file_size) && <p className="mt-1 text-caption text-neutral-400">{formatSize(d.file_size)}</p>}
                </div>
                {d.file_url ? (
                  <a
                    href={d.file_url}
                    download
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex h-10 flex-shrink-0 items-center gap-2 rounded bg-navy px-4 text-body-sm font-semibold text-white hover:bg-navy-light"
                  >
                    <DownloadIcon className="h-4 w-4" /> Download
                  </a>
                ) : (
                  d.requires_inquiry && (
                    <button
                      type="button"
                      onClick={() => setRequestTarget(d)}
                      className="flex h-10 flex-shrink-0 items-center gap-2 rounded bg-navy px-4 text-body-sm font-semibold text-white hover:bg-navy-light"
                    >
                      <Lock className="h-4 w-4" /> Get Download
                    </button>
                  )
                )}
              </Card>
            ))}
          </div>
        </AsyncState>
      </div>

      {requestTarget && (
        <RequestDownloadModal
          open
          onClose={() => setRequestTarget(null)}
          downloadId={requestTarget.id}
          downloadTitle={requestTarget.title}
        />
      )}
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
