import { describe, it, expect } from "vitest";
import { render, screen } from "@testing-library/react";
import { MemoryRouter } from "react-router-dom";
import { SmartLink } from "@/components/public/SmartLink";

/**
 * Audit fix (Medium remediation) — covers the exact bug this component
 * fixes: a CMS-authored external URL (Hero/CTA button, footer quick
 * link) previously fed straight into React Router's <Link>, which only
 * ever performs a client-side navigate() and produces a broken in-app
 * "route" instead of actually leaving the site.
 */
describe("SmartLink", () => {
  it("renders a plain anchor for an absolute URL", () => {
    render(
      <MemoryRouter>
        <SmartLink to="https://partner-university.example/apply">Apply at our partner</SmartLink>
      </MemoryRouter>
    );

    const link = screen.getByRole("link", { name: "Apply at our partner" });
    expect(link.tagName).toBe("A");
    expect(link).toHaveAttribute("href", "https://partner-university.example/apply");
    expect(link).toHaveAttribute("target", "_blank");
    expect(link).toHaveAttribute("rel", "noopener noreferrer");
  });

  it("renders a protocol-relative URL as external too", () => {
    render(
      <MemoryRouter>
        <SmartLink to="//partner-university.example/apply">Apply</SmartLink>
      </MemoryRouter>
    );

    expect(screen.getByRole("link", { name: "Apply" })).toHaveAttribute("target", "_blank");
  });

  it("renders a React Router Link for an internal path", () => {
    render(
      <MemoryRouter>
        <SmartLink to="/courses/bsc-computer-science">View course</SmartLink>
      </MemoryRouter>
    );

    const link = screen.getByRole("link", { name: "View course" });
    expect(link).toHaveAttribute("href", "/courses/bsc-computer-science");
    expect(link).not.toHaveAttribute("target");
  });
});
