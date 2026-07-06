/**
 * A single, narrow wrapper around token persistence. Nothing else in the
 * app touches localStorage directly — if the storage strategy ever needs
 * to change (e.g. to an httpOnly cookie issued by a future BFF layer),
 * this is the only file that changes.
 */
const TOKEN_KEY = "pnkc_auth_token";

export const tokenStorage = {
  get(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  },
  set(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
  },
  clear(): void {
    localStorage.removeItem(TOKEN_KEY);
  },
};
