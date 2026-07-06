import { useState } from "react";
import { Search } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { AsyncState } from "@/components/public/AsyncState";
import { Accordion } from "@/components/public/Accordion";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { EmptyState } from "@/components/ui";
import type { Faq as FaqEntry, FaqCategory } from "@/types/faq";

/** The global Site FAQ (Milestone 17) — searchable, filterable by category, distinct from any single Course's own FAQ tab. */
export function Faq() {
  const [search, setSearch] = useState("");
  const [searchTerm, setSearchTerm] = useState("");
  const [categorySlug, setCategorySlug] = useState("");

  const { data: categories } = usePublicDetail<FaqCategory[]>(ENDPOINTS.faqCategories.public);

  const params = new URLSearchParams();
  if (searchTerm) params.set("search", searchTerm);
  if (categorySlug) params.set("category", categorySlug);
  const query = params.toString();
  const endpoint = query ? `${ENDPOINTS.faqs.publicList}?${query}` : ENDPOINTS.faqs.publicList;
  const { data: faqs, isLoading, error } = usePublicDetail<FaqEntry[]>(endpoint);

  // FAQPage schema only reflects the base, unfiltered list — a filtered/searched
  // view isn't "the page's canonical content" in the way structured data implies.
  const showFaqSchema = !searchTerm && !categorySlug && (faqs?.length ?? 0) > 0;

  useSeoHead({
    title: "Frequently Asked Questions",
    canonicalPath: "/faq",
    jsonLd: showFaqSchema
      ? {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          mainEntity: (faqs ?? []).map((f) => ({
            "@type": "Question",
            name: f.question,
            acceptedAnswer: { "@type": "Answer", text: f.answer },
          })),
        }
      : null,
  });

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "FAQ" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Frequently Asked Questions</h1>
      <p className="mt-2 text-body text-neutral-500">Find quick answers to common questions about admissions, fees, and campus life.</p>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          setSearchTerm(search);
        }}
        className="relative mt-6"
      >
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search frequently asked questions..."
          className="h-11 w-full rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] pl-10 pr-3 text-body"
        />
      </form>

      {categories && categories.length > 0 && (
        <div className="mt-4 flex flex-wrap gap-2">
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
          isEmpty={(faqs ?? []).length === 0}
          emptyState={<EmptyState title="No matching questions" description="Try a different search term or category." />}
        >
          <Accordion items={(faqs ?? []).map((f) => ({ id: f.id, question: f.question, answer: f.answer }))} />
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
