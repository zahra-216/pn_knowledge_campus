import { Component, type ErrorInfo, type ReactNode } from "react";
import { AlertTriangle } from "lucide-react";

interface ErrorBoundaryProps {
  children: ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
}

/**
 * Only a class component can hook `getDerivedStateFromError`/
 * `componentDidCatch` — no hook equivalent exists. Without this, a
 * render-time exception anywhere in the tree unmounts the whole app to
 * a blank white screen with no recovery action.
 */
export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  state: ErrorBoundaryState = { hasError: false };

  static getDerivedStateFromError(): ErrorBoundaryState {
    return { hasError: true };
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error("Unhandled render error:", error, info.componentStack);
  }

  render() {
    if (!this.state.hasError) {
      return this.props.children;
    }

    return (
      <div className="mx-auto flex max-w-xl flex-col items-center gap-4 px-4 py-32 text-center">
        <AlertTriangle className="h-14 w-14 text-gold" aria-hidden="true" />
        <h1 className="font-display text-h1 font-semibold text-[color:var(--color-text)]">Something Went Wrong</h1>
        <p className="text-body text-neutral-500">
          An unexpected error occurred. Reloading the page usually fixes it — if it keeps happening, please let us
          know.
        </p>
        <button
          type="button"
          onClick={() => {
            window.location.href = "/";
          }}
          className="mt-2 inline-flex h-10 items-center justify-center rounded bg-navy px-4 text-body-sm font-semibold text-white transition-colors hover:bg-navy-light"
        >
          Reload Page
        </button>
      </div>
    );
  }
}
