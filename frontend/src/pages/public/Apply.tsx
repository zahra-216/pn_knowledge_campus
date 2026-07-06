import { useEffect, useState, type FormEvent } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { CheckCircle2, Upload, Trash2, FileText, Clock } from "lucide-react";
import { api } from "@/lib/api";
import { ENDPOINTS } from "@/lib/endpoints";
import { usePublicList } from "@/hooks/usePublicList";
import { useSeoHead } from "@/hooks/useSeoHead";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Button, Card, Input, Switch, useToast } from "@/components/ui";
import type { ApiError, ApiResponse } from "@/types/api";
import type { Application, ApplicationCreatePayload, ApplicationUpdatePayload } from "@/types/application";
import type { Course } from "@/types/course";

const STORAGE_KEY = "pnkc_application";

interface StoredCredential {
  application_number: string;
  email: string;
}

function loadStoredCredential(): StoredCredential | null {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? (JSON.parse(raw) as StoredCredential) : null;
  } catch {
    return null;
  }
}

function storeCredential(applicationNumber: string, email: string) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ application_number: applicationNumber, email }));
}

const STATUS_LABEL: Record<string, string> = {
  submitted: "Submitted — awaiting review",
  under_review: "Under Review",
  approved: "Approved",
  rejected: "Not Successful",
};

/**
 * Online Applications (Milestone 20) — no visitor login exists, so a
 * draft is tracked client-side via application_number + email (also
 * cached in localStorage so returning to this page resumes it). See
 * the backend Application model's docblock for the full design.
 */
export function Apply() {
  const [searchParams] = useSearchParams();
  const { items: courses } = usePublicList<Course>(ENDPOINTS.courses.publicList, { per_page: 100 });

  const [application, setApplication] = useState<Application | null>(null);
  const [credentialEmail, setCredentialEmail] = useState("");
  const [isLoading, setIsLoading] = useState(true);

  useSeoHead({ title: "Apply Now", canonicalPath: "/apply" });

  useEffect(() => {
    const stored = loadStoredCredential();
    if (!stored) {
      setIsLoading(false);
      return;
    }

    api
      .post<ApiResponse<Application>>(ENDPOINTS.applications.lookup, stored)
      .then(({ data }) => {
        setApplication(data.data);
        setCredentialEmail(stored.email);
      })
      .catch(() => localStorage.removeItem(STORAGE_KEY))
      .finally(() => setIsLoading(false));
  }, []);

  function handleStarted(created: Application, email: string) {
    setApplication(created);
    setCredentialEmail(email);
    storeCredential(created.application_number, email);
  }

  function handleUpdated(updated: Application, email: string) {
    setApplication(updated);
    if (email !== credentialEmail) {
      setCredentialEmail(email);
      storeCredential(updated.application_number, email);
    }
  }

  if (isLoading) {
    return (
      <div className="mx-auto max-w-2xl px-4 py-24 text-center text-body-sm text-neutral-500">Loading your application...</div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Apply" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Apply Now</h1>
      <p className="mt-2 text-body text-neutral-500">
        Start your application below. You can save your progress and come back later before submitting.
      </p>
      <p className="mt-1 text-body-sm text-neutral-500">
        Already started an application?{" "}
        <Link to="/apply/resume" className="font-semibold text-navy hover:underline dark:text-gold">
          Resume it here
        </Link>
        .
      </p>

      <div className="mt-8">
        {!application ? (
          <StartForm defaultCourseSlug={searchParams.get("course")} courses={courses} onStarted={handleStarted} />
        ) : application.status !== "draft" ? (
          <StatusView application={application} />
        ) : (
          <EditForm application={application} credentialEmail={credentialEmail} courses={courses} onUpdated={handleUpdated} />
        )}
      </div>
    </div>
  );
}

function StartForm({
  defaultCourseSlug,
  courses,
  onStarted,
}: {
  defaultCourseSlug: string | null;
  courses: Course[];
  onStarted: (application: Application, email: string) => void;
}) {
  const { showToast } = useToast();
  const [form, setForm] = useState({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    course_id: courses.find((c) => c.slug === defaultCourseSlug)?.id ?? null,
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (defaultCourseSlug && !form.course_id) {
      const match = courses.find((c) => c.slug === defaultCourseSlug);
      if (match) setForm((f) => ({ ...f, course_id: match.id }));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [courses, defaultCourseSlug]);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setIsSaving(true);
    setErrors({});
    try {
      const payload: ApplicationCreatePayload = { ...form };
      const { data } = await api.post<ApiResponse<Application>>(ENDPOINTS.applications.create, payload);
      onStarted(data.data, form.email);
      showToast("Application started — you can now fill in the rest and upload documents.", "success");
    } catch (err) {
      const apiError = err as ApiError;
      setErrors(apiError.errors ?? {});
      showToast(apiError.message, "error");
    } finally {
      setIsSaving(false);
    }
  }

  return (
    <Card>
      <form onSubmit={handleSubmit} className="flex flex-col gap-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="First name"
            required
            value={form.first_name}
            onChange={(e) => setForm({ ...form, first_name: e.target.value })}
            error={errors.first_name?.[0]}
          />
          <Input
            label="Last name"
            required
            value={form.last_name}
            onChange={(e) => setForm({ ...form, last_name: e.target.value })}
            error={errors.last_name?.[0]}
          />
        </div>
        <Input
          type="email"
          label="Email"
          required
          value={form.email}
          onChange={(e) => setForm({ ...form, email: e.target.value })}
          error={errors.email?.[0]}
          hint="You'll use this together with your application number to check back later."
        />
        <Input label="Phone (optional)" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />

        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">Course (optional)</span>
          <select
            value={form.course_id ?? ""}
            onChange={(e) => setForm({ ...form, course_id: e.target.value ? Number(e.target.value) : null })}
            className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
          >
            <option value="">Select a course</option>
            {courses.map((c) => (
              <option key={c.id} value={c.id}>
                {c.course_name}
              </option>
            ))}
          </select>
        </label>

        <Button type="submit" isLoading={isSaving} className="mt-2 self-start">
          Start Application
        </Button>
      </form>
    </Card>
  );
}

function EditForm({
  application,
  credentialEmail,
  courses,
  onUpdated,
}: {
  application: Application;
  credentialEmail: string;
  courses: Course[];
  onUpdated: (application: Application, email: string) => void;
}) {
  const { showToast } = useToast();
  const [form, setForm] = useState({
    first_name: application.first_name,
    last_name: application.last_name,
    phone: application.phone ?? "",
    date_of_birth: application.date_of_birth ?? "",
    nationality: application.nationality ?? "",
    address: application.address ?? "",
    international_applicant: application.international_applicant,
    course_id: application.course?.id ?? null,
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSaving, setIsSaving] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSaveDraft(e: FormEvent) {
    e.preventDefault();
    setIsSaving(true);
    setErrors({});
    try {
      const payload: ApplicationUpdatePayload = { ...form, email: credentialEmail };
      const { data } = await api.put<ApiResponse<Application>>(ENDPOINTS.applications.update(application.application_number), payload);
      onUpdated(data.data, credentialEmail);
      showToast("Draft saved.", "success");
    } catch (err) {
      const apiError = err as ApiError;
      setErrors(apiError.errors ?? {});
      showToast(apiError.message, "error");
    } finally {
      setIsSaving(false);
    }
  }

  async function handleSubmitApplication() {
    if (application.documents.length === 0) {
      showToast("Please upload at least one document before submitting.", "error");
      return;
    }
    setIsSubmitting(true);
    try {
      const { data } = await api.post<ApiResponse<Application>>(ENDPOINTS.applications.submit(application.application_number), {
        email: credentialEmail,
      });
      onUpdated(data.data, credentialEmail);
      showToast("Application submitted successfully!", "success");
    } catch (err) {
      showToast((err as ApiError).message, "error");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="flex flex-col gap-6">
      <Card className="border-gold/40 bg-gold/5">
        <p className="text-body-sm text-neutral-600">
          Your application number is <strong className="text-[color:var(--color-text)]">{application.application_number}</strong>.
          Keep it along with your email ({credentialEmail}) to resume later.
        </p>
      </Card>

      <Card>
        <form onSubmit={handleSaveDraft} className="flex flex-col gap-4">
          <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Personal Information</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="First name" required value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} error={errors.first_name?.[0]} />
            <Input label="Last name" required value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} error={errors.last_name?.[0]} />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Phone" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
            <Input type="date" label="Date of birth" value={form.date_of_birth} onChange={(e) => setForm({ ...form, date_of_birth: e.target.value })} />
          </div>
          <Input label="Nationality" value={form.nationality} onChange={(e) => setForm({ ...form, nationality: e.target.value })} />
          <Input label="Address" value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} />

          <label className="flex flex-col gap-1.5">
            <span className="text-body-sm font-medium text-[color:var(--color-text)]">Course</span>
            <select
              value={form.course_id ?? ""}
              onChange={(e) => setForm({ ...form, course_id: e.target.value ? Number(e.target.value) : null })}
              className="h-10 rounded-sm border border-[color:var(--color-border)] bg-[color:var(--color-surface)] px-3 text-body"
            >
              <option value="">Select a course</option>
              {courses.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.course_name}
                </option>
              ))}
            </select>
          </label>

          <Switch
            label="I am an international applicant"
            checked={form.international_applicant}
            onChange={(checked) => setForm({ ...form, international_applicant: checked })}
          />

          <Button type="submit" variant="secondary" isLoading={isSaving} className="self-start">
            Save Draft
          </Button>
        </form>
      </Card>

      <DocumentsPanel application={application} credentialEmail={credentialEmail} onChanged={(app) => onUpdated(app, credentialEmail)} />

      <Card className="flex flex-col items-start gap-3">
        <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Ready to submit?</h2>
        <p className="text-body-sm text-neutral-500">
          Once submitted, your application can no longer be edited. Make sure everything above is correct and you've uploaded all required documents.
        </p>
        <Button onClick={handleSubmitApplication} isLoading={isSubmitting}>
          Submit Application
        </Button>
      </Card>
    </div>
  );
}

function DocumentsPanel({
  application,
  credentialEmail,
  onChanged,
}: {
  application: Application;
  credentialEmail: string;
  onChanged: (application: Application) => void;
}) {
  const { showToast } = useToast();
  const [label, setLabel] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  async function handleUpload(e: FormEvent) {
    e.preventDefault();
    if (!file) return;

    setIsUploading(true);
    try {
      const formData = new FormData();
      formData.append("email", credentialEmail);
      formData.append("label", label);
      formData.append("file", file);

      await api.post(ENDPOINTS.applications.documents(application.application_number), formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      const { data } = await api.post<{ data: Application }>(ENDPOINTS.applications.lookup, {
        application_number: application.application_number,
        email: credentialEmail,
      });
      onChanged(data.data);
      setLabel("");
      setFile(null);
      showToast("Document uploaded.", "success");
    } catch (err) {
      showToast((err as ApiError).message, "error");
    } finally {
      setIsUploading(false);
    }
  }

  async function handleDelete(documentId: number) {
    await api.delete(ENDPOINTS.applications.document(application.application_number, documentId), {
      data: { email: credentialEmail },
    });
    const { data } = await api.post<{ data: Application }>(ENDPOINTS.applications.lookup, {
      application_number: application.application_number,
      email: credentialEmail,
    });
    onChanged(data.data);
  }

  return (
    <Card>
      <h2 className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Documents</h2>
      <p className="mt-1 text-body-sm text-neutral-500">Upload your transcripts, ID, and any other required documents (PDF, JPG, or PNG, max 5MB each).</p>

      {application.documents.length > 0 && (
        <ul className="mt-4 flex flex-col gap-2">
          {application.documents.map((doc) => (
            <li key={doc.id} className="flex items-center gap-3 rounded border border-[color:var(--color-border)] px-3 py-2">
              <FileText className="h-5 w-5 flex-shrink-0 text-navy dark:text-gold" />
              <div className="flex-1">
                <p className="text-body-sm font-medium text-[color:var(--color-text)]">{doc.label}</p>
                <p className="text-caption text-neutral-500">{doc.file_name}</p>
              </div>
              <button type="button" onClick={() => handleDelete(doc.id)} aria-label={`Remove ${doc.label}`}>
                <Trash2 className="h-4 w-4 text-neutral-400 hover:text-danger" />
              </button>
            </li>
          ))}
        </ul>
      )}

      <form onSubmit={handleUpload} className="mt-4 flex flex-wrap items-end gap-3">
        <Input label="Label" placeholder="e.g. Transcript" value={label} onChange={(e) => setLabel(e.target.value)} className="min-w-[160px] flex-1" />
        <label className="flex flex-col gap-1.5">
          <span className="text-body-sm font-medium text-[color:var(--color-text)]">File</span>
          <input
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            onChange={(e) => setFile(e.target.files?.[0] ?? null)}
            className="text-body-sm"
          />
        </label>
        <Button type="submit" variant="secondary" isLoading={isUploading} disabled={!file || !label}>
          <Upload className="h-4 w-4" /> Upload
        </Button>
      </form>
    </Card>
  );
}

function StatusView({ application }: { application: Application }) {
  const isFinal = application.status === "approved" || application.status === "rejected";

  return (
    <Card className="flex flex-col items-center gap-3 py-10 text-center">
      {isFinal ? <CheckCircle2 className="h-12 w-12 text-gold" /> : <Clock className="h-12 w-12 text-navy dark:text-gold" />}
      <p className="font-display text-h3 font-semibold text-[color:var(--color-text)]">{STATUS_LABEL[application.status]}</p>
      <p className="text-body-sm text-neutral-500">Application reference: {application.application_number}</p>
      {application.status === "rejected" && (
        <p className="max-w-md text-body-sm text-neutral-500">
          We'll always consider you for future intakes — feel free to explore our other courses.
        </p>
      )}
    </Card>
  );
}
