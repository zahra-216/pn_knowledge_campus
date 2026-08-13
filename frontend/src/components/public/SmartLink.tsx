import type { ReactNode } from "react";
import { Link } from "react-router-dom";

function isExternalUrl(url: string): boolean {
  return /^[a-z][a-z0-9+.-]*:/i.test(url) || url.startsWith("//");
}

interface SmartLinkProps {
  to: string;
  className?: string;
  children: ReactNode;
}

/**
 * Audit fix (Medium remediation) — several CMS free-text URL fields
 * (Hero/CTA buttons, footer quick links) feed a content-editor-authored
 * URL directly into React Router's <Link>, with nothing in the editor
 * UI warning against entering an absolute external URL. <Link> only
 * ever performs a client-side `navigate()`, which produces a broken
 * in-app "route" for an absolute URL instead of actually leaving the
 * site. Renders a plain <a> (opened in a new tab, since none of these
 * blocks has its own "open in new tab" toggle the way menu items do)
 * for anything that looks like an absolute URL, <Link> otherwise.
 */
export function SmartLink({ to, className, children }: SmartLinkProps) {
  if (isExternalUrl(to)) {
    return (
      <a href={to} target="_blank" rel="noopener noreferrer" className={className}>
        {children}
      </a>
    );
  }

  return (
    <Link to={to} className={className}>
      {children}
    </Link>
  );
}
