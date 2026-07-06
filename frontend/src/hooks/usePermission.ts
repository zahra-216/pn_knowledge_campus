import { useCallback } from "react";
import { useAuth } from "@/context/AuthContext";

/**
 * Every permission key follows the `{module}.{action}` convention seeded
 * in the backend's RoleSeeder (e.g. "users.delete"). This hook is the
 * single place a component asks "can the current user do this?" — it
 * mirrors the SRS Permission Matrix, just evaluated client-side for UI
 * purposes (show/hide a button). The backend Policy is still the actual
 * authority; this only controls what's rendered, never what's allowed.
 *
 * `can`/`canAny` are memoized (keyed on `user`, which is a stable
 * useState reference from AuthContext that only changes on actual
 * login/logout) rather than being plain closures recreated every
 * render. Nearly every list/editor page across the admin does
 * `useEffect(() => { ... fetch ... }, [fetchX, can])` — if `can` were a
 * new function identity every render, that effect would re-fire after
 * every state update the fetch itself causes (isLoading, data), which
 * re-renders the component, which creates a new `can`, which re-fires
 * the effect — an infinite fetch loop, not just a wasted re-render.
 */
export function usePermission() {
  const { user } = useAuth();

  const can = useCallback(
    (permission: string): boolean => {
      if (!user) return false;
      if (user.role === "Super Admin") return true;
      return user.permissions.includes(permission);
    },
    [user]
  );

  const canAny = useCallback((permissions: string[]): boolean => permissions.some(can), [can]);

  return { can, canAny };
}
