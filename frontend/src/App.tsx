import { BrowserRouter } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { ToastProvider } from "@/components/ui";
import { AppRoutes } from "@/routes/AppRoutes";

/**
 * Provider order matters: ToastProvider outermost so any provider below
 * it (including AuthContext's own error handling) can call useToast;
 * AuthProvider next so every route can call useAuth.
 */
export function App() {
  return (
    <BrowserRouter>
      <ToastProvider>
        <AuthProvider>
          <AppRoutes />
        </AuthProvider>
      </ToastProvider>
    </BrowserRouter>
  );
}
