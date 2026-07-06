/**
 * Matches the standard response envelope defined in the API Design
 * document, Section 4. Every API call in this project should type its
 * response as ApiResponse<T> or ApiCollection<T> rather than inventing a
 * one-off shape per call.
 */
export interface ApiResponse<T> {
  data: T;
}

export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface ApiCollection<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
}

/**
 * Standard error envelope (API Design, Section 6.2). `errors` is only
 * present on 422 validation failures.
 */
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  required_permission?: string;
  conflict?: { type: string; related_resource: string; count: number };
  /** HTTP status of the failed response, when one exists (e.g. network errors have none). Lets callers tell a 404 (render Not Found) apart from any other failure (render an error state). */
  status?: number;
}
