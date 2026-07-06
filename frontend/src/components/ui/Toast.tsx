import { createContext, useCallback, useContext, useState, type ReactNode } from "react";
import { CheckCircle2, AlertCircle, Info, XCircle, X } from "lucide-react";
import { cn } from "@/utils/cn";

type ToastTone = "success" | "error" | "info" | "warning";

interface Toast {
  id: number;
  message: string;
  tone: ToastTone;
}

interface ToastContextValue {
  showToast: (message: string, tone?: ToastTone) => void;
}

const ToastContext = createContext<ToastContextValue | undefined>(undefined);

const TONE_ICON: Record<ToastTone, typeof CheckCircle2> = {
  success: CheckCircle2,
  error: XCircle,
  warning: AlertCircle,
  info: Info,
};

const TONE_CLASS: Record<ToastTone, string> = {
  success: "border-success/30 text-success",
  error: "border-danger/30 text-danger",
  warning: "border-warning/30 text-warning",
  info: "border-info/30 text-info",
};

/**
 * Component Library, Section 6.4 — Toast/Notification. Auto-dismisses
 * after 4s except errors, which persist until the user dismisses them,
 * exactly per the UI/UX Design spec.
 */
export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const showToast = useCallback((message: string, tone: ToastTone = "info") => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, message, tone }]);

    if (tone !== "error") {
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
      }, 4000);
    }
  }, []);

  const dismiss = (id: number) => setToasts((prev) => prev.filter((t) => t.id !== id));

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}
      <div className="fixed right-4 top-4 z-50 flex w-80 flex-col gap-2" role="region" aria-label="Notifications">
        {toasts.map((toast) => {
          const Icon = TONE_ICON[toast.tone];
          return (
            <div
              key={toast.id}
              role="alert"
              className={cn(
                "flex items-start gap-2 rounded border bg-[color:var(--color-surface)] p-3 text-body-sm shadow-2",
                TONE_CLASS[toast.tone]
              )}
            >
              <Icon className="h-5 w-5 flex-shrink-0" aria-hidden="true" />
              <p className="flex-1 text-[color:var(--color-text)]">{toast.message}</p>
              <button
                onClick={() => dismiss(toast.id)}
                aria-label="Dismiss notification"
                className="text-neutral-400 hover:text-neutral-600"
              >
                <X className="h-4 w-4" />
              </button>
            </div>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components -- co-locating the hook with its Provider is the standard Context pattern
export function useToast(): ToastContextValue {
  const ctx = useContext(ToastContext);
  if (!ctx) throw new Error("useToast must be used within a ToastProvider");
  return ctx;
}
