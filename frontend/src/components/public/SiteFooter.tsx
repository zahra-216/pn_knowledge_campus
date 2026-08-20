import { Mail, Phone, MapPin, Facebook, Twitter, Instagram, Linkedin, Youtube, Globe } from "lucide-react";
import type { LucideIcon } from "lucide-react";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { SmartLink } from "@/components/public/SmartLink";
import { Container } from "@/components/public/Container";

const PLATFORM_ICON: Record<string, LucideIcon> = {
  facebook: Facebook,
  twitter: Twitter,
  x: Twitter,
  instagram: Instagram,
  linkedin: Linkedin,
  youtube: Youtube,
};

// Trimmed to the 4 links that actually matter for a compact footer —
// edit this list directly to add/remove links without touching the
// Menu Builder's full "footer" menu (which still powers other consumers).
const ALLOWED_QUICK_LINK_LABELS = ["About", "News", "Contact", "Privacy Policy"];

export function SiteFooter() {
  const { settings, isLoading: settingsLoading, footerMenu: menu, socialLinks } = usePublicSettings();
  const logoMediaId = settings.logo_media_id != null ? Number(settings.logo_media_id) : undefined;
  const resolvedMedia = useResolvedMedia([logoMediaId]);
  const logo = resolvedMedia.get(logoMediaId as number);
  const brandStillLoading = settingsLoading || (!!logoMediaId && !logo);

  const campusName = (settings.campus_short_name as string) || (settings.campus_name as string) || "PNK Global Campus";
  const year = new Date().getFullYear();
  const copyright = (settings.footer_copyright as string) || `© ${year} ${campusName}. All rights reserved.`;
  const quickLinks = (menu?.items ?? []).filter((item) => ALLOWED_QUICK_LINK_LABELS.includes(item.label));

  return (
    <footer className="bg-[color:var(--pub-ink)] text-white">
      <Container size="wide" className="flex flex-col gap-8 py-10 lg:flex-row lg:items-start lg:justify-between lg:gap-6">
        <div className="flex flex-col gap-3">
          {brandStillLoading ? (
            <span aria-hidden="true" className="inline-block h-16 w-40 animate-pulse rounded-sm bg-white/10" />
          ) : logo ? (
            <img
              src={logo.thumb_url ?? logo.url}
              alt={campusName}
              className="rounded-sm bg-white object-contain p-2"
              style={{ height: 64, width: "auto" }}
            />
          ) : (
            <p className="font-display text-h4 font-medium">{campusName}</p>
          )}

          {socialLinks && socialLinks.length > 0 && (
            <div className="flex items-center gap-4">
              {socialLinks.map((link) => {
                const Icon = PLATFORM_ICON[link.platform.toLowerCase()] ?? Globe;
                return (
                  <a
                    key={link.id}
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={link.platform}
                    className="text-white/50 transition-colors hover:text-gold"
                  >
                    <Icon className="h-4 w-4" />
                  </a>
                );
              })}
            </div>
          )}
        </div>

        <div className="flex flex-col gap-3">
          <p className="text-caption font-semibold uppercase tracking-[0.14em] text-gold">Quick Links</p>
          <nav className="flex flex-col gap-2" aria-label="Footer">
            {quickLinks.map((item) => (
              <SmartLink key={item.id} to={item.url ?? "#"} className="text-body-sm text-white/70 hover:text-white">
                {item.label}
              </SmartLink>
            ))}
          </nav>
        </div>

        <div className="flex flex-col gap-3">
          <p className="text-caption font-semibold uppercase tracking-[0.14em] text-gold">Contact</p>
          <div className="flex flex-col gap-2 text-body-sm text-white/70">
            {(settings.contact_address as string) && (
              <span className="flex items-start gap-2">
                <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-white/40" />
                {settings.contact_address as string}
              </span>
            )}
            {(settings.contact_phone as string) &&
              (settings.contact_phone as string)
                .split(",")
                .map((phone) => phone.trim())
                .filter(Boolean)
                .map((phone, i) => (
                  <a key={phone} href={`tel:${phone}`} className="flex items-center gap-2 hover:text-white">
                    {i === 0 ? <Phone className="h-4 w-4 flex-shrink-0 text-white/40" /> : <span className="h-4 w-4 flex-shrink-0" />}
                    {phone}
                  </a>
                ))}
            {(settings.contact_email as string) &&
              (settings.contact_email as string)
                .split(",")
                .map((email) => email.trim())
                .filter(Boolean)
                .map((email, i) => (
                  <a key={email} href={`mailto:${email}`} className="flex items-center gap-2 hover:text-white">
                    {i === 0 ? <Mail className="h-4 w-4 flex-shrink-0 text-white/40" /> : <span className="h-4 w-4 flex-shrink-0" />}
                    {email}
                  </a>
                ))}
        </div>
      </div>
      </Container>

      <div className="border-t border-white/10">
        <Container size="wide" className="flex flex-col items-center gap-2 py-4 text-center text-caption text-white/50 sm:flex-row sm:justify-between sm:text-left">
          <span>{copyright}</span>
        </Container>
      </div>
    </footer>
  );
}