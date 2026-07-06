/**
 * Matches UserResource on the backend (app/Http/Resources/UserResource.php).
 * `role` is a plain string, not a literal union of the five baseline
 * SRS roles (Permission Matrix, Section 10) — Super Admins can create
 * custom roles beyond those five (see the Users, Roles & Permissions
 * module), so any role name is possible here.
 */
export const BASELINE_ROLES = ["Super Admin", "Administrator", "Content Editor", "Marketing", "Admissions"] as const;

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  is_active: boolean;
  role: string | null;
  permissions: string[];
  last_login_at: string | null;
}

export interface LoginPayload {
  email: string;
  password: string;
  device_name: string;
}

export interface LoginResponseData {
  token: string;
  user: AuthUser;
}
