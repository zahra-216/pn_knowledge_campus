import { useEffect } from "react";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import type { SeoMeta } from "@/types/seo";

interface UseSeoHeadOptions {
  title: string;
  description?: string | null;
  canonicalPath?: string;
  imageUrl?: string | null;
  type?: "website" | "article";
  seo?: SeoMeta | null;
  jsonLd?: Record<string, unknown> | null;
}

const TAG_ID_PREFIX = "pnkc-seo-";

function setMeta(attr: "name" | "property", key: string, content: string | null | undefined) {
  const id = `${TAG_ID_PREFIX}${attr}-${key}`;
  let tag = document.head.querySelector<HTMLMetaElement>(`meta[data-seo-id="${id}"]`);

  if (!content) {
    tag?.remove();
    return;
  }

  if (!tag) {
    tag = document.createElement("meta");
    tag.setAttribute(attr, key);
    tag.setAttribute("data-seo-id", id);
    document.head.appendChild(tag);
  }
  tag.setAttribute("content", content);
}

function setLink(rel: string, href: string | null | undefined) {
  const id = `${TAG_ID_PREFIX}link-${rel}`;
  let tag = document.head.querySelector<HTMLLinkElement>(`link[data-seo-id="${id}"]`);

  if (!href) {
    tag?.remove();
    return;
  }

  if (!tag) {
    tag = document.createElement("link");
    tag.setAttribute("rel", rel);
    tag.setAttribute("data-seo-id", id);
    document.head.appendChild(tag);
  }
  tag.setAttribute("href", href);
}

/**
 * Client-side <head> sync for SPA route changes — this is the "keep the
 * visible title/meta accurate as a real browsing session navigates"
 * layer, not a replacement for server-rendered meta tags. Crawlers that
 * don't execute JS (Facebook/Twitter/LinkedIn/Slack unfurlers) only ever
 * see whatever HTML the server actually returned; satisfying that fully
 * requires a Laravel-rendered meta shell (see the approved Public
 * Website architecture plan) which wasn't built in this pass — flagged
 * explicitly rather than silently only doing the client-side half.
 *
 * Reads each entity's own embedded `seo` object (SeoMetaResource, only
 * present on public detail responses) when given, falling back to the
 * page's own title/description otherwise.
 */
export function useSeoHead({ title, description, canonicalPath, imageUrl, type = "website", seo, jsonLd }: UseSeoHeadOptions) {
  // Per-entity OG/Twitter images (audit fix — these fields didn't exist
  // before) take priority over the page's own generic imageUrl, the
  // same "seo.X falls back to the page's own value" pattern already
  // used for title/description below.
  const media = useResolvedMedia([seo?.og_image_media_id, seo?.twitter_image_media_id]);
  const ogImage = (seo?.og_image_media_id ? media.get(seo.og_image_media_id)?.url : undefined) ?? imageUrl;
  const twitterImage = (seo?.twitter_image_media_id ? media.get(seo.twitter_image_media_id)?.url : undefined) ?? imageUrl;

  useEffect(() => {
    const resolvedDescription = seo?.meta_description || description || undefined;
    const canonical = seo?.canonical_url || (canonicalPath ? `${window.location.origin}${canonicalPath}` : undefined);

    // Audit fix (Medium remediation) — a custom seo.seo_title (set by an
    // admin) is a complete title on its own; unconditionally appending
    // "| PNK Global Campus" here duplicated the suffix whenever one was
    // already baked into that custom title (e.g. course/faculty detail
    // pages), and disagreed with the server-rendered SEO shell, which
    // only appends the suffix for the natural-fallback case (see
    // SeoShellResolver::buildMeta() on the backend — this mirrors it).
    const resolvedTitle = seo?.seo_title || `${title} | PNK Global Campus`;
    document.title = resolvedTitle;

    setMeta("name", "description", resolvedDescription);
    setMeta("name", "keywords", seo?.keywords);
    setMeta("name", "robots", seo ? [seo.robots_index ? "index" : "noindex", seo.robots_follow ? "follow" : "nofollow"].join(", ") : undefined);
    setLink("canonical", canonical);

    setMeta("property", "og:title", seo?.og_title || resolvedTitle);
    setMeta("property", "og:description", seo?.og_description || resolvedDescription);
    setMeta("property", "og:type", type);
    setMeta("property", "og:image", ogImage);
    setMeta("property", "og:url", canonical);

    setMeta("name", "twitter:card", twitterImage ? "summary_large_image" : "summary");
    setMeta("name", "twitter:title", seo?.twitter_title || resolvedTitle);
    setMeta("name", "twitter:description", seo?.twitter_description || resolvedDescription);
    setMeta("name", "twitter:image", twitterImage);

    const scriptId = `${TAG_ID_PREFIX}jsonld`;
    let script = document.head.querySelector<HTMLScriptElement>(`script[data-seo-id="${scriptId}"]`);
    if (jsonLd) {
      if (!script) {
        script = document.createElement("script");
        script.type = "application/ld+json";
        script.setAttribute("data-seo-id", scriptId);
        document.head.appendChild(script);
      }
      script.textContent = JSON.stringify(jsonLd);
    } else {
      script?.remove();
    }
  }, [title, description, canonicalPath, ogImage, twitterImage, type, seo, jsonLd]);
}
