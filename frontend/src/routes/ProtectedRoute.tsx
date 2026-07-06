import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import { Spinner } from "@/components/ui";

/**
 * Wraps every /admin/* route. An unauthenticated visit redirects to
 * /login and remembers where the user was headed, so login sends them
 * back to it (UI/UX Design flow: "unauthenticated visit to /admin
 * redirects to /login" — Development Roadmap, Milestone 0 testing
 * checklist).
 */
export function ProtectedRoute() {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <Spinner />
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location.pathname }} replace />;
  }

  return <Outlet />;
}
