import { useEffect, useRef, useState, type ReactNode, type CSSProperties } from "react";
import { cn } from "@/utils/cn";

interface RevealProps {
  children: ReactNode;
  className?: string;
  /** Stagger delay in ms — for a list of Reveal siblings entering together. */
  delay?: number;
}

/**
 * Public Site Redesign, Stage 1 — the site's only scroll-triggered
 * motion: a modest fade + rise, fired once, respecting
 * prefers-reduced-motion globally via the `.reveal` rule in index.css.
 * Falls back to visible-immediately if IntersectionObserver isn't
 * available (older browsers, jsdom in tests) rather than hiding content.
 */
export function Reveal({ children, className, delay = 0 }: RevealProps) {
  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    if (typeof IntersectionObserver === "undefined") {
      setVisible(true);
      return;
    }
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.15, rootMargin: "0px 0px -10% 0px" }
    );
    observer.observe(el);
    return () => observer.disconnect();
  }, []);

  const style: CSSProperties | undefined = delay ? { transitionDelay: `${delay}ms` } : undefined;

  return (
    <div ref={ref} className={cn("reveal", visible && "is-visible", className)} style={style}>
      {children}
    </div>
  );
}
