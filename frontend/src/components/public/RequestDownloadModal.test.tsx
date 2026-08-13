import { describe, it, expect, vi, beforeEach, afterAll } from "vitest";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { RequestDownloadModal } from "@/components/public/RequestDownloadModal";
import { ToastProvider } from "@/components/ui";
import { api } from "@/lib/api";

vi.mock("@/lib/api", () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

const originalOpen = window.open;

/**
 * Audit fix (High remediation) — covers the gated-download capture flow
 * built as part of this same audit pass (DownloadController::requestDownload()).
 * This is a genuine business-critical path (every gated Prospectus/form
 * download funnels through it), not a UI primitive, so it's a
 * proportionate place to spend key-flow test coverage.
 */
describe("RequestDownloadModal", () => {
  beforeEach(() => {
    vi.mocked(api.post).mockReset();
    window.open = vi.fn();
  });

  afterAll(() => {
    window.open = originalOpen;
  });

  it("submits name/email and opens the returned signed URL", async () => {
    vi.mocked(api.post).mockResolvedValueOnce({
      data: { data: { url: "https://example.test/downloads/1/file?signature=abc" } },
    });
    const user = userEvent.setup();

    render(
      <ToastProvider>
        <RequestDownloadModal open onClose={() => {}} downloadId={1} downloadTitle="Undergraduate Prospectus" />
      </ToastProvider>
    );

    await user.type(screen.getByLabelText("Full name"), "Jane Doe");
    await user.type(screen.getByLabelText("Email"), "jane@example.com");
    await user.click(screen.getByRole("button", { name: "Get Download Link" }));

    await vi.waitFor(() => {
      expect(api.post).toHaveBeenCalledWith(
        "/downloads/1/request",
        expect.objectContaining({ name: "Jane Doe", email: "jane@example.com" })
      );
    });
    await vi.waitFor(() => {
      expect(window.open).toHaveBeenCalledWith(
        "https://example.test/downloads/1/file?signature=abc",
        "_blank",
        "noopener,noreferrer"
      );
    });
  });

  it("shows a toast and does not open a URL when the request fails server-side", async () => {
    vi.mocked(api.post).mockRejectedValueOnce({
      message: "Too many requests. Please try again in a minute.",
    });
    const user = userEvent.setup();

    render(
      <ToastProvider>
        <RequestDownloadModal open onClose={() => {}} downloadId={1} downloadTitle="Undergraduate Prospectus" />
      </ToastProvider>
    );

    await user.type(screen.getByLabelText("Full name"), "Jane Doe");
    await user.type(screen.getByLabelText("Email"), "jane@example.com");
    await user.click(screen.getByRole("button", { name: "Get Download Link" }));

    expect(await screen.findByRole("alert")).toHaveTextContent("Too many requests. Please try again in a minute.");
    expect(window.open).not.toHaveBeenCalled();
  });
});
