import { useEffect } from "react";

const SCRIPT_ID_PREFIX = "pnkc-analytics-";

function appendScript(id: string, build: (script: HTMLScriptElement) => void) {
  if (document.head.querySelector(`script[data-analytics-id="${id}"]`)) return;

  const script = document.createElement("script");
  script.setAttribute("data-analytics-id", id);
  build(script);
  document.head.appendChild(script);
}

/**
 * Audit fix (High remediation) — Settings > Analytics (AnalyticsTab.tsx)
 * has captured `ga_tracking_id`/`gtm_container_id` since Milestone 15,
 * but nothing ever read them back out and injected the actual GA4/GTM
 * loader scripts into the page; the settings were saved and then never
 * used. Runs once per page load (not per-route) — each ID either is or
 * isn't configured for the whole site, so there's nothing to react to
 * on navigation. Follows useSeoHead.ts's own "create once, tag with a
 * data-* id" convention for imperative <head> DOM manipulation.
 */
export function useAnalytics(gaTrackingId?: string | null, gtmContainerId?: string | null) {
  useEffect(() => {
    if (gaTrackingId) {
      appendScript(`${SCRIPT_ID_PREFIX}ga-loader`, (script) => {
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaTrackingId)}`;
      });
      appendScript(`${SCRIPT_ID_PREFIX}ga-init`, (script) => {
        script.textContent = `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${gaTrackingId}');`;
      });
    }

    if (gtmContainerId) {
      appendScript(`${SCRIPT_ID_PREFIX}gtm-loader`, (script) => {
        script.textContent = `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','${gtmContainerId}');`;
      });
    }
  }, [gaTrackingId, gtmContainerId]);
}
