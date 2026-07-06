import { useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { useSeoHead } from "@/hooks/useSeoHead";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Button, Card, Input, useToast } from "@/components/ui";
import type { ApiError, ApiResponse } from "@/types/api";
import type { Application } from "@/types/application";

const STORAGE_KEY = "pnkc_application";

/** Resumes a draft/submitted application by application_number + email — see Apply.tsx for the full design rationale. */
export function ApplyResume() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [applicationNumber, setApplicationNumber] = useState("");
  const [email, setEmail] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  useSeoHead({ title: "Resume Your Application", canonicalPath: "/apply/resume" });

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setIsLoading(true);
    setError(null);
    try {
      await api.post<ApiResponse<Application>>(ENDPOINTS.applications.lookup, {
        application_number: applicationNumber.trim(),
        email: email.trim(),
      });
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ application_number: applicationNumber.trim(), email: email.trim() }));
      showToast("Application found — continuing where you left off.", "success");
      navigate("/apply");
    } catch (err) {
      const apiError = err as ApiError;
      setError(apiError.message || "We couldn't find an application matching that reference number and email.");
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <div className="mx-auto max-w-md px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Apply", to: "/apply" }, { label: "Resume" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Resume Your Application</h1>
      <p className="mt-2 text-body text-neutral-500">Enter your application number and the email address you applied with.</p>

      <Card className="mt-8">
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
          <Input
            label="Application Number"
            placeholder="PNKC-2026-000123"
            required
            value={applicationNumber}
            onChange={(e) => setApplicationNumber(e.target.value)}
          />
          <Input type="email" label="Email" required value={email} onChange={(e) => setEmail(e.target.value)} />
          {error && <p className="text-body-sm text-danger">{error}</p>}
          <Button type="submit" isLoading={isLoading}>
            Continue
          </Button>
        </form>
      </Card>
    </div>
  );
}
