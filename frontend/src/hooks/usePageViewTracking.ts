import { useEffect } from "react";
import { useLocation } from "react-router-dom";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";

const VISITOR_ID_KEY = "pnkc_visitor_id";

/**
 * Milestone 24 (Dashboard Analytics) — the other half of PageView's
 * migration docblock. `visitor_id` is a random, cookie-less identifier
 * generated once and kept in localStorage purely to let the admin
 * Dashboard tell "unique visitors" apart from "page views" — it carries
 * no PII and isn't used for anything else (no cross-site tracking, no
 * fingerprinting).
 */
function getOrCreateVisitorId(): string {
  let id = localStorage.getItem(VISITOR_ID_KEY);
  if (!id) {
    id = crypto.randomUUID();
    localStorage.setItem(VISITOR_ID_KEY, id);
  }
  return id;
}

export function usePageViewTracking() {
  const location = useLocation();

  useEffect(() => {
    api
      .post(ENDPOINTS.analytics.pageview, {
        path: location.pathname,
        visitor_id: getOrCreateVisitorId(),
      })
      .catch(() => {
        // A tracking ping failing (offline, ad-blocker, ...) must never
        // affect the visitor's actual browsing experience.
      });
  }, [location.pathname]);
}
