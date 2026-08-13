import { describe, it, expect, vi, beforeEach } from "vitest";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Routes, Route } from "react-router-dom";
import { Login } from "@/pages/auth/Login";
import { AuthProvider } from "@/context/AuthContext";
import { ToastProvider } from "@/components/ui";
import { tokenStorage } from "@/lib/storage";
import { api } from "@/lib/api";

vi.mock("@/lib/api", () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

/**
 * Audit fix (High remediation) — this project had zero frontend test
 * coverage. Login is the one screen every admin user depends on, so
 * it's the highest-value place to start: a real end-to-end render of
 * AuthProvider + Login, mocking only the network boundary (`api`).
 */
describe("Login", () => {
  beforeEach(() => {
    localStorage.clear();
    vi.mocked(api.get).mockReset();
    vi.mocked(api.post).mockReset();
  });

  function renderLogin() {
    return render(
      <ToastProvider>
        <AuthProvider>
          <MemoryRouter initialEntries={["/login"]}>
            <Routes>
              <Route path="/login" element={<Login />} />
              <Route path="/admin" element={<div>Admin Home</div>} />
            </Routes>
          </MemoryRouter>
        </AuthProvider>
      </ToastProvider>
    );
  }

  it("logs in with correct credentials and redirects to /admin", async () => {
    vi.mocked(api.post).mockResolvedValueOnce({
      data: { data: { token: "fake-token", user: { id: 1, name: "Jane Staff", email: "jane@example.com" } } },
    });
    const user = userEvent.setup();
    renderLogin();

    await user.type(screen.getByLabelText("Email"), "jane@example.com");
    await user.type(screen.getByLabelText("Password"), "correct-password");
    await user.click(screen.getByRole("button", { name: "Sign in" }));

    await waitFor(() => expect(screen.getByText("Admin Home")).toBeInTheDocument());
    expect(tokenStorage.get()).toBe("fake-token");
    expect(api.post).toHaveBeenCalledWith(
      "/auth/login",
      expect.objectContaining({ email: "jane@example.com", password: "correct-password" })
    );
  });

  it("shows the field error returned by the API on invalid credentials", async () => {
    vi.mocked(api.post).mockRejectedValueOnce({
      message: "Validation failed.",
      errors: { email: ["These credentials do not match our records."] },
    });
    const user = userEvent.setup();
    renderLogin();

    await user.type(screen.getByLabelText("Email"), "jane@example.com");
    await user.type(screen.getByLabelText("Password"), "wrong-password");
    await user.click(screen.getByRole("button", { name: "Sign in" }));

    expect(await screen.findByText("These credentials do not match our records.")).toBeInTheDocument();
    expect(tokenStorage.get()).toBeNull();
  });
});
