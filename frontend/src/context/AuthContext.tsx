import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { tokenStorage } from "@/lib/storage";
import type { ApiResponse } from "@/types/api";
import type { AuthUser, LoginPayload, LoginResponseData } from "@/types/auth";

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (payload: LoginPayload) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

/**
 * Wraps the whole app (see src/App.tsx). Owns the one piece of global
 * state every screen needs: who is logged in and what they're allowed to
 * do. Talks to the four /auth/* endpoints from the API Design document
 * and nothing else — module-specific state (Courses, News, ...) belongs
 * in that module's own hooks, not here.
 */
export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchCurrentUser = useCallback(async () => {
    try {
      const { data } = await api.get<ApiResponse<AuthUser>>(ENDPOINTS.auth.me);
      setUser(data.data);
    } catch {
      tokenStorage.clear();
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    if (tokenStorage.get()) {
      fetchCurrentUser();
    } else {
      setIsLoading(false);
    }
  }, [fetchCurrentUser]);

  const login = useCallback(async (payload: LoginPayload) => {
    const { data } = await api.post<ApiResponse<LoginResponseData>>(ENDPOINTS.auth.login, payload);
    tokenStorage.set(data.data.token);
    setUser(data.data.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post(ENDPOINTS.auth.logout);
    } finally {
      tokenStorage.clear();
      setUser(null);
    }
  }, []);

  return (
    <AuthContext.Provider value={{ user, isLoading, isAuthenticated: user !== null, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components -- co-locating the hook with its Provider is the standard Context pattern
export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within an AuthProvider");
  return ctx;
}
