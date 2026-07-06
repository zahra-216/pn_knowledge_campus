import { useEffect, type ReactNode } from "react";
import { X } from "lucide-react";
import { cn } from "@/utils/cn";

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
  footer?: ReactNode;
  size?: "form" | "wide";
}

/**
 * Component Library, Section 6.4 — Modal. Max width 560px for forms,
 * 800px for the Media Picker (`size="wide"`). Closes on Escape and on
 * backdrop click, per the standard overlay interaction pattern.
 */
export function Modal({ open, onClose, title, children, footer, size = "form" }: ModalProps) {
  useEffect(() => {
    if (!open) return;

    function handleKeyDown(e: KeyboardEvent) {
      if (e.key === "Escape") onClose();
    }

    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" role="presentation" onClick={onClose}>
      <div
        role="dialog"
        aria-modal="true"
        aria-label={title}
        onClick={(e) => e.stopPropagation()}
        className={cn(
          "flex max-h-[90vh] w-full flex-col rounded-lg bg-[color:var(--color-surface)] shadow-3",
          size === "form" ? "max-w-[560px]" : "max-w-[800px]"
        )}
      >
        <div className="flex items-center justify-between border-b border-[color:var(--color-border)] px-6 py-4">
          <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">{title}</h2>
          <button
            type="button"
            onClick={onClose}
            aria-label="Close"
            className="rounded p-1.5 text-neutral-500 hover:bg-black/5 dark:hover:bg-white/5"
          >
            <X className="h-5 w-5" aria-hidden="true" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-4">{children}</div>

        {footer && <div className="flex justify-end gap-2 border-t border-[color:var(--color-border)] px-6 py-4">{footer}</div>}
      </div>
    </div>
  );
}
