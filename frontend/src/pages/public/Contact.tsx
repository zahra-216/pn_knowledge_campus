import { useState, type FormEvent } from "react";
import { Mail, Phone, MapPin } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useSeoHead } from "@/hooks/useSeoHead";
import { ENDPOINTS } from "@/lib/endpoints";
import { api } from "@/lib/api";
import { Breadcrumb } from "@/components/public/Breadcrumb";
import { Button, Card, Input, Textarea, useToast } from "@/components/ui";
import type { Branch } from "@/types/branch";
import type { OfficeHour } from "@/types/officeHours";
import { dayLabel } from "@/types/officeHours";
import type { ApiError, ApiResponse } from "@/types/api";
import type { InquiryConfirmation, InquiryPayload } from "@/types/inquiry";

const EMPTY_FORM = { name: "", email: "", phone: "", message: "" };

export function Contact() {
  const { settings } = usePublicSettings();
  const { data: branches } = usePublicDetail<Branch[]>(ENDPOINTS.branches.public);
  const { data: officeHours } = usePublicDetail<OfficeHour[]>(ENDPOINTS.officeHours.public);
  const { showToast } = useToast();

  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  useSeoHead({ title: "Contact", canonicalPath: "/contact" });

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    const payload: InquiryPayload = { ...form, source_page: "/contact" };

    try {
      await api.post<ApiResponse<InquiryConfirmation>>(ENDPOINTS.inquiries.submit, payload);
      showToast("Thanks for reaching out — we'll be in touch shortly.", "success");
      setForm(EMPTY_FORM);
    } catch (err) {
      const apiError = err as ApiError;
      setErrors(apiError.errors ?? {});
      showToast(apiError.message, "error");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Contact" }]} />
      <h1 className="mt-4 font-display text-h1 font-semibold text-[color:var(--color-text)]">Contact Us</h1>
      <p className="mt-2 max-w-xl text-body text-neutral-500">
        Have a question about admissions, courses, or campus life? Send us a message and our team will get back to you.
      </p>

      <div className="mt-10 grid gap-10 lg:grid-cols-2">
        <Card>
          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <Input label="Full name" required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} error={errors.name?.[0]} />
            <Input
              type="email"
              label="Email"
              required
              value={form.email}
              onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
              error={errors.email?.[0]}
            />
            <Input
              type="tel"
              label="Phone (optional)"
              value={form.phone}
              onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))}
              error={errors.phone?.[0]}
            />
            <Textarea
              label="Message"
              required
              rows={5}
              value={form.message}
              onChange={(e) => setForm((f) => ({ ...f, message: e.target.value }))}
              error={errors.message?.[0]}
            />
            <Button type="submit" isLoading={isSubmitting} className="mt-2 self-start">
              Send Message
            </Button>
          </form>
        </Card>

        <div className="flex flex-col gap-6">
          <Card className="flex flex-col gap-3">
            {(settings.contact_address as string) && (
              <p className="flex items-start gap-2 text-body-sm text-neutral-600">
                <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-navy dark:text-gold" /> {settings.contact_address as string}
              </p>
            )}
            {(settings.contact_phone as string) && (
              <a href={`tel:${settings.contact_phone}`} className="flex items-center gap-2 text-body-sm text-neutral-600 hover:text-navy">
                <Phone className="h-4 w-4 flex-shrink-0 text-navy dark:text-gold" /> {settings.contact_phone as string}
              </a>
            )}
            {(settings.contact_email as string) && (
              <a href={`mailto:${settings.contact_email}`} className="flex items-center gap-2 text-body-sm text-neutral-600 hover:text-navy">
                <Mail className="h-4 w-4 flex-shrink-0 text-navy dark:text-gold" /> {settings.contact_email as string}
              </a>
            )}
          </Card>

          {officeHours && officeHours.length > 0 && (
            <Card>
              <p className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Office Hours</p>
              <dl className="mt-3 flex flex-col gap-1.5">
                {officeHours.map((h) => (
                  <div key={h.day} className="flex justify-between text-body-sm">
                    <dt className="text-neutral-500">{dayLabel(h.day)}</dt>
                    <dd className="text-[color:var(--color-text)]">{h.is_open ? `${h.opens_at} – ${h.closes_at}` : "Closed"}</dd>
                  </div>
                ))}
              </dl>
            </Card>
          )}

          {(settings.google_maps_embed_url as string) && (
            <div className="overflow-hidden rounded-lg border border-[color:var(--color-border)]">
              <iframe
                src={settings.google_maps_embed_url as string}
                title="Campus location"
                className="h-64 w-full"
                loading="lazy"
              />
            </div>
          )}

          {branches && branches.length > 1 && (
            <Card>
              <p className="font-display text-h4 font-semibold text-[color:var(--color-text)]">Our Branches</p>
              <div className="mt-3 flex flex-col gap-4">
                {branches.map((b) => (
                  <div key={b.id}>
                    <p className="font-semibold text-body-sm text-[color:var(--color-text)]">{b.name}</p>
                    <p className="text-body-sm text-neutral-500">
                      {b.address}, {b.city}
                    </p>
                  </div>
                ))}
              </div>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
}
