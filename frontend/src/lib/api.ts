import axios, { type AxiosError } from "axios";
import { tokenStorage } from "@/lib/storage";
import type { ApiError } from "@/types/api";

/**
 * The single Axios instance every part of the app uses to talk to the
 * backend. Two responsibilities live here and nowhere else:
 *
 *  1. Attach the Sanctum bearer token to every request (API Design,
 *     Section 2.3) — callers never set the Authorization header
 *     themselves.
 *  2. Normalize every failure into the ApiError shape from
 *     src/types/api.ts, and force a logout on 401 (an expired/revoked
 *     token), so every screen can handle errors the same way regardless
 *     of which endpoint failed.
 */
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api/v1",
  headers: {
    Accept: "application/json",
  },
});

api.interceptors.request.use((config) => {
  const token = tokenStorage.get();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/**
 * A 401 anywhere in the app means the token is gone/expired/revoked
 * (API Design, Section 6.1). Rather than have every screen check for
 * this, we clear the token and hard-redirect to /login once, here.
 */
api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiError>) => {
    if (error.response?.status === 401) {
      tokenStorage.clear();
      if (window.location.pathname !== "/login") {
        window.location.assign("/login");
      }
    }

    return Promise.reject(normalizeApiError(error));
  }
);

/**
 * Guarantees every thrown error has at least a `.message` string, even if
 * the failure was a network error with no response body at all — so a
 * catch block never has to defensively check for undefined.
 */
function normalizeApiError(error: AxiosError<ApiError>): ApiError {
  if (error.response?.data?.message) {
    return { ...error.response.data, status: error.response.status };
  }

  if (error.code === "ERR_NETWORK") {
    return { message: "Could not reach the server. Check your connection and try again." };
  }

  return { message: "Something went wrong. Please try again.", status: error.response?.status };
}
