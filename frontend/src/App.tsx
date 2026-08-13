import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { ToastProvider } from "@/components/ui";
import { ErrorBoundary } from "@/components/ErrorBoundary";
import { AppRoutes } from "@/routes/AppRoutes";

/**
 * Provider order matters: ToastProvider outermost so any provider below
 * it (including AuthContext's own error handling) can call useToast;
 * AuthProvider next so every route can call useAuth. ErrorBoundary sits
 * inside both so its fallback UI can still use design tokens without
 * assuming either provider is in a healthy state.
 */
export function App() {
  return (
    <BrowserRouter>
      <ToastProvider>
        <AuthProvider>
          <ErrorBoundary>
            <AppRoutes />
          </ErrorBoundary>
        </AuthProvider>
      </ToastProvider>
    </BrowserRouter>
  );
}
