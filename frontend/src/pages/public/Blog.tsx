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
import type { BlogCategory, BlogPost } from "@/types/blog";

export function Blog() {
  const [params, setParams] = useSearchParams();
  const [searchInput, setSearchInput] = useState(params.get("search") ?? "");
  const page = Number(params.get("page") ?? 1);

  const { data: categories } = usePublicDetail<BlogCategory[]>(ENDPOINTS.blogCategories.public);
  const { items: posts, meta, isLoading, error } = usePublicList<BlogPost>(ENDPOINTS.blog.publicList, {
    page,
    per_page: 9,
    "filter[category]": params.get("category") ?? undefined,
    search: params.get("search") ?? undefined,
  });

  useSeoHead({ title: "Blog", canonicalPath: "/blog" });

  function updateFilter(key: string, value: string) {
    const next = new URLSearchParams(params);
    if (value) next.set(key, value);
    else next.delete(key);
    next.delete("page");
    setParams(next);
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Blog" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Blog</h1>

      <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div className="flex flex-wrap gap-2">
          <FilterChip label="All" active={!params.get("category")} onClick={() => updateFilter("category", "")} />
          {(categories ?? []).map((c) => (
            <FilterChip key={c.id} label={c.name} active={params.get("category") === c.slug} onClick={() => updateFilter("category", c.slug)} />
          ))}
        </div>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            updateFilter("search", searchInput);
          }}
          className="relative"
        >
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
          <input
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            placeholder="Search blog..."
            className="h-10 w-64 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-9 pr-3 text-body"
          />
        </form>
      </div>

      <div className="mt-8">
        <AsyncState
          isLoading={isLoading}
          error={error}
          isEmpty={posts.length === 0}
          emptyState={<EmptyState title="No posts found" />}
        >
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {posts.map((p) => (
              <ContentCard key={p.id} to={`/blog/${p.slug}`} image={p.featured_image_url} title={p.title} meta={p.category?.name} excerpt={p.excerpt} />
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
