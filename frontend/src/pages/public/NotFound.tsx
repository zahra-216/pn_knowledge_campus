import { Link } from "react-router-dom";
import { Compass } from "lucide-react";
import { useSeoHead } from "@/hooks/useSeoHead";

export function NotFound() {
  useSeoHead({ title: "Page Not Found", canonicalPath: window.location.pathname });

  return (
    <div className="mx-auto flex max-w-xl flex-col items-center gap-4 px-4 py-32 text-center">
      <Compass className="h-14 w-14 text-gold" aria-hidden="true" />
      <h1 className="font-display text-h1 font-semibold text-[color:var(--color-text)]">Page Not Found</h1>
      <p className="text-body text-neutral-500">
        The page you're looking for doesn't exist or may have been moved.
      </p>
      <Link
        to="/"
        className="mt-2 inline-flex h-10 items-center justify-center rounded bg-navy px-4 text-body-sm font-semibold text-white transition-colors hover:bg-navy-light"
      >
        Back to Home
      </Link>
    </div>
  );
}
