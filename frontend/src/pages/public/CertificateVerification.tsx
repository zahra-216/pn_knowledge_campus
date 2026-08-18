import { useSeoHead } from "@/hooks/useSeoHead";
import { Breadcrumb } from "@/components/public/Breadcrumb";

export function CertificateVerification() {
  useSeoHead({ title: "Certificate Verification", canonicalPath: "/student-life/certificate-verification" });

  return (
    <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Student Life" }, { label: "Certificate Verification" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Certificate Verification</h1>
      <p className="mt-2 text-body text-neutral-500">Content coming soon.</p>
    </div>
  );
}