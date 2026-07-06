/**
 * The global Site FAQ (Milestone 17) — distinct from `CourseFaq` in
 * types/course.ts, which is the pre-existing course-scoped sub-resource
 * embedded in Course. Both are backed by the same `faqs` table on the
 * backend (see Faq::scopeGlobal()), but the two are managed through
 * separate admin screens and separate API endpoints.
 */

/** Matches FaqCategoryResource on the backend. Flat taxonomy — no hierarchy/media/SEO, same shape as BlogCategory/NewsCategory/PartnerCategory. */
export interface FaqCategory {
  id: number;
  name: string;
  slug: string;
  order: number;
  faqs_count?: number;
}

export type FaqCategoryPayload = Partial<Pick<FaqCategory, "name" | "order">> & { slug?: string };

/** Matches FaqResource on the backend (as returned by the global FaqController, not CourseFaqController). */
export interface Faq {
  id: number;
  question: string;
  answer: string;
  category: { id: number; name: string; slug: string } | null;
  order: number;
  is_active: boolean;
}

export type FaqPayload = Partial<Pick<Faq, "question" | "answer" | "order" | "is_active">> & {
  category_id?: number | null;
};
