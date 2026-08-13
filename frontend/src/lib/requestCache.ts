/**
 * Public Site Perf Fix — a lot of public data (faculties, departments,
 * course categories/levels, the header menu, site settings) barely
 * changes between requests, but `usePublicList`/`usePublicDetail` had no
 * caching at all: every mount of every component re-fetched, and
 * navigating Home -> About -> Home re-fetched everything from scratch.
 * That's the root cause of the request pile-up and long spinner on first
 * load, and it kept happening on every subsequent navigation too.
 *
 * This is intentionally NOT a dependency like React Query — just a
 * module-level Map keyed by "endpoint + serialized params", holding
 * promises in flight (so two components requesting the same thing at
 * the same time share one network call) and resolved values for a
 * short TTL (so re-mounting a page shortly after leaving it is instant).
 */

type CacheEntry<T> = {
  promise: Promise<T>;
  resolvedAt: number | null;
};

const CACHE_TTL_MS = 2 * 60 * 1000; // 2 minutes — long enough to help navigation, short enough to stay fresh

const cache = new Map<string, CacheEntry<unknown>>();

export function cacheKey(endpoint: string, params?: Record<string, unknown>): string {
  return params ? `${endpoint}?${JSON.stringify(params)}` : endpoint;
}

/**
 * Returns a cached/in-flight promise for `key` if it's still within TTL,
 * otherwise calls `fetcher()`, stores the promise, and returns it.
 * A failed fetch is evicted immediately so the next call retries.
 */
export function getCached<T>(key: string, fetcher: () => Promise<T>): Promise<T> {
  const existing = cache.get(key) as CacheEntry<T> | undefined;
  const isFresh = existing && (existing.resolvedAt === null || Date.now() - existing.resolvedAt < CACHE_TTL_MS);
  if (existing && isFresh) {
    return existing.promise;
  }

  const entry: CacheEntry<T> = { promise: fetcher(), resolvedAt: null };
  cache.set(key, entry);

  entry.promise
    .then(() => {
      entry.resolvedAt = Date.now();
    })
    .catch(() => {
      cache.delete(key);
    });

  return entry.promise;
}

/** Escape hatch for places that must always hit the network (e.g. after a mutation). */
export function invalidateCached(key: string): void {
  cache.delete(key);
}

/** Test-only: wipe the whole cache so each test starts from a clean slate. */
export function clearRequestCache(): void {
  cache.clear();
}