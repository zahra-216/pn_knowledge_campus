import { useCallback, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { FileText, Lock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { Breadcrumb } from "@/layouts/AdminLayout";
import { Card, Badge, Button, Textarea, Spinner, useToast } from "@/components/ui";
import type { BadgeTone } from "@/components/ui/Badge";
import { usePermission } from "@/hooks/usePermission";
import type { ApiResponse } from "@/types/api";
import type { AdminApplication, ApplicationDocument, ApplicationStatus } from "@/types/application";

const STATUS_TONE: Record<ApplicationStatus, BadgeTone> = {
  draft: "neutral",
  submitted: "info",
  under_review: "warning",
  approved: "success",
  rejected: "danger",
};

export function ApplicationDetail() {
  const { id } = useParams<{ id: string }>();
  const { can } = usePermission();
  const { showToast } = useToast();
  const canReview = can("applications.review");

  const [application, setApplication] = useState<AdminApplication | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [reviewNotes, setReviewNotes] = useState("");
  const [actionPending, setActionPending] = useState<string | null>(null);

  const fetchApplication = useCallback(async () => {
    setIsLoading(true);
    try {
      const { data } = await api.get<ApiResponse<AdminApplication>>(ENDPOINTS.applications.adminShow(Number(id)));
      setApplication(data.data);
      setReviewNotes(data.data.review_notes ?? "");
    } finally {
      setIsLoading(false);
    }
  }, [id]);

  useEffect(() => {
    if (!can("applications.view")) return;
    fetchApplication();
  }, [fetchApplication, can]);

  async function handleMarkUnderReview() {
    setActionPending("under_review");
    try {
      const { data } = await api.patch<ApiResponse<AdminApplication>>(ENDPOINTS.applications.adminUnderReview(Number(id)));
      setApplication(data.data);
      showToast("Marked as under review.", "success");
    } catch {
      showToast("Could not update this application.", "error");
    } finally {
      setActionPending(null);
    }
  }

  async function handleApprove() {
    setActionPending("approve");
    try {
      const { data } = await api.patch<ApiResponse<AdminApplication>>(ENDPOINTS.applications.adminApprove(Number(id)), {
        review_notes: reviewNotes || undefined,
      });
      setApplication(data.data);
      showToast("Application approved.", "success");
    } catch {
      showToast("Could not approve this application.", "error");
    } finally {
      setActionPending(null);
    }
  }

  async function handleReject() {
    if (!reviewNotes.trim()) {
      showToast("Please provide a reason before rejecting.", "error");
      return;
    }
    setActionPending("reject");
    try {
      const { data } = await api.patch<ApiResponse<AdminApplication>>(ENDPOINTS.applications.adminReject(Number(id)), {
        review_notes: reviewNotes,
      });
      setApplication(data.data);
      showToast("Application rejected.", "success");
    } catch {
      showToast("Could not reject this application.", "error");
    } finally {
      setActionPending(null);
    }
  }

  /**
   * A plain `<a href>` navigation wouldn't carry the Sanctum bearer
   * token (it's an Authorization header the `api` axios instance
   * attaches, not a cookie) — the same reason Applications.tsx's own
   * CSV export fetches through `api` as a blob instead of linking
   * directly. Documents live on a private disk since the audit's
   * Critical PII finding, so this authenticated fetch is now required,
   * not just a nicety.
   */
  async function handleDownloadDocument(doc: ApplicationDocument) {
    try {
      const { data } = await api.get(doc.url, { responseType: "blob" });
      const url = URL.createObjectURL(data as Blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = doc.file_name;
      link.click();
      URL.revokeObjectURL(url);
    } catch {
      showToast("Could not download this document.", "error");
    }
  }

  if (!can("applications.view")) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Applications", to: "/admin/applications" }, { label: "Review" }]} />
        <Card>
          <div className="flex items-center gap-2 text-body-sm text-neutral-500">
            <Lock className="h-4 w-4" aria-hidden="true" />
            You don't have access to Applications.
          </div>
        </Card>
      </div>
    );
  }

  if (isLoading || !application) {
    return (
      <div className="flex flex-col gap-4">
        <Breadcrumb items={[{ label: "Applications", to: "/admin/applications" }, { label: "Review" }]} />
        <div className="flex justify-center py-16">
          <Spinner />
        </div>
      </div>
    );
  }

  const isFinal = application.status === "approved" || application.status === "rejected";

  return (
    <div className="flex flex-col gap-4">
      <Breadcrumb items={[{ label: "Applications", to: "/admin/applications" }, { label: application.application_number }]} />

      <div className="flex items-center justify-between">
        <div>
          <h1 className="font-display text-h2 font-semibold text-[color:var(--color-text)]">
            {application.first_name} {application.last_name}
          </h1>
          <p className="text-body-sm text-neutral-500">{application.application_number}</p>
        </div>
        <Badge tone={STATUS_TONE[application.status]}>{application.status.replace("_", " ")}</Badge>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <Card className="flex flex-col gap-2">
          <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Applicant Details</h2>
          <DetailRow label="Email" value={application.email} />
          <DetailRow label="Phone" value={application.phone ?? "—"} />
          <DetailRow label="Date of Birth" value={application.date_of_birth ?? "—"} />
          <DetailRow label="Nationality" value={application.nationality ?? "—"} />
          <DetailRow label="Address" value={application.address ?? "—"} />
          <DetailRow label="International Applicant" value={application.international_applicant ? "Yes" : "No"} />
          <DetailRow label="Course" value={application.course?.name ?? "—"} />
          <DetailRow label="Submitted" value={application.submitted_at ? new Date(application.submitted_at).toLocaleString() : "—"} />
        </Card>

        <Card className="flex flex-col gap-3">
          <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Documents</h2>
          {application.documents.length === 0 ? (
            <p className="text-body-sm text-neutral-500">No documents uploaded.</p>
          ) : (
            <ul className="flex flex-col gap-2">
              {application.documents.map((doc) => (
                <li key={doc.id}>
                  <button
                    type="button"
                    onClick={() => handleDownloadDocument(doc)}
                    className="flex items-center gap-2 text-body-sm text-navy hover:underline dark:text-gold"
                  >
                    <FileText className="h-4 w-4 flex-shrink-0" />
                    {doc.label} — {doc.file_name}
                  </button>
                </li>
              ))}
            </ul>
          )}

          {application.reviewed_by && (
            <p className="mt-2 text-caption text-neutral-500">
              Last reviewed by {application.reviewed_by.name}
              {application.reviewed_at && ` on ${new Date(application.reviewed_at).toLocaleString()}`}
            </p>
          )}
        </Card>
      </div>

      {canReview && (
        <Card className="flex flex-col gap-4">
          <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Review</h2>
          <Textarea
            label="Review notes"
            hint="Required when rejecting; optional when approving."
            rows={4}
            value={reviewNotes}
            onChange={(e) => setReviewNotes(e.target.value)}
            disabled={isFinal}
          />
          <div className="flex flex-wrap gap-3">
            {application.status === "submitted" && (
              <Button variant="secondary" onClick={handleMarkUnderReview} isLoading={actionPending === "under_review"}>
                Mark Under Review
              </Button>
            )}
            {!isFinal && (
              <>
                <Button onClick={handleApprove} isLoading={actionPending === "approve"}>
                  Approve
                </Button>
                <Button variant="danger" onClick={handleReject} isLoading={actionPending === "reject"}>
                  Reject
                </Button>
              </>
            )}
          </div>
        </Card>
      )}
    </div>
  );
}

function DetailRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between gap-4 text-body-sm">
      <span className="text-neutral-500">{label}</span>
      <span className="text-right text-[color:var(--color-text)]">{value}</span>
    </div>
  );
}
