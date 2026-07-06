/** Matches UserResource, as returned by the admin Users, Roles & Permissions CRUD (not the /auth/me shape — see types/auth.ts's AuthUser for that). */
export interface AdminUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  is_active: boolean;
  role: string | null;
  permissions: string[];
  last_login_at: string | null;
}

export interface UserCreatePayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string | null;
  role: string;
  is_active?: boolean;
}

export type UserUpdatePayload = Partial<Omit<UserCreatePayload, "password_confirmation">> & { password_confirmation?: string };

/** Matches RoleResource. */
export interface AdminRole {
  id: number;
  name: string;
  permissions: string[];
  users_count: number;
}

export interface RolePayload {
  name?: string;
  permissions?: string[];
}

/** Matches RoleController::permissions() — every permission key grouped by its module prefix. */
export type PermissionsByModule = Record<string, string[]>;
