import { Mail, Phone, MapPin, Facebook, Twitter, Instagram, Linkedin, Youtube, Globe } from "lucide-react";
import type { LucideIcon } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { useResolvedMedia } from "@/hooks/useResolvedMedia";
import { ENDPOINTS } from "@/lib/endpoints";
import { SmartLink } from "@/components/public/SmartLink";
import { Container } from "@/components/public/Container";
import type { Menu } from "@/types/menu";
import type { SocialLink } from "@/types/socialLink";

const PLATFORM_ICON: Record<string, LucideIcon> = {
  facebook: Facebook,
  twitter: Twitter,
  x: Twitter,
  instagram: Instagram,
  linkedin: Linkedin,
  youtube: Youtube,
};

/**
 * Public Site Redesign, Stage 1 — the site's closing "ink" statement:
 * a large tagline moment up top, then quieter rule-divided columns
 * instead of the old dense uniform 4-column box. Same Settings/Menu/
 * SocialLinks data sources as before — nothing here is hardcoded.
 */
export function SiteFooter() {
  const { data: menu } = usePublicDetail<Menu>(ENDPOINTS.menus.public("footer"));
  const { data: socialLinks } = usePublicDetail<SocialLink[]>(ENDPOINTS.socialLinks.public);
  const { settings, isLoading: settingsLoading } = usePublicSettings();
  const logoMediaId = settings.logo_media_id as number | undefined;
  const resolvedMedia = useResolvedMedia([logoMediaId]);
  const logo = resolvedMedia.get(logoMediaId as number);
  const brandStillLoading = settingsLoading || (!!logoMediaId && !logo);

  const campusName = (settings.campus_short_name as string) || (settings.campus_name as string) || "PNK Global Campus";
  const tagline = settings.campus_tagline as string | undefined;
  const footerText = settings.footer_text as string | undefined;
  const year = new Date().getFullYear();
  const copyright = (settings.footer_copyright as string) || `© ${year} ${campusName}. All rights reserved.`;
  const quickLinks = menu?.items ?? [];
  const footerLogoHeight = Number(settings.footer_logo_height) || 40;

  return (
    <footer className="bg-[color:var(--pub-ink)] text-white">
      <Container size="wide" className="py-[var(--space-md)]">
        {tagline && (
          <p className="max-w-3xl text-balance font-display text-h1 font-medium leading-tight">{tagline}</p>
        )}

        <div className="mt-16 grid gap-10 border-t border-white/10 pt-12 lg:grid-cols-12">
          <div className="lg:col-span-5">
            {brandStillLoading ? (
              <span aria-hidden="true" className="inline-block h-10 w-32 animate-pulse rounded-sm bg-white/10" />
            ) : logo ? (
              // The header shows the logo in its natural colour; here it
              // sits on the footer's dark ink background instead, so it's
              // forced to solid white via filter (brightness(0) turns
              // every opaque pixel black, invert flips that to white) —
              // no separate "footer logo" asset to manage in the Media
              // Library, just the one logo rendered two ways.
              <img
                src={logo.thumb_url ?? logo.url}
                alt={campusName}
                className="w-auto brightness-0 invert"
                style={{ height: footerLogoHeight }}
              />
            ) : (
              <p className="font-display text-h4 font-medium">{campusName}</p>
            )}
            {footerText && <p className="mt-3 max-w-sm text-body-sm text-white/60">{footerText}</p>}

            {socialLinks && socialLinks.length > 0 && (
              <div className="mt-6 flex items-center gap-5">
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

          <div className="lg:col-span-3 lg:border-l lg:border-white/10 lg:pl-10">
            <p className="text-caption font-semibold uppercase tracking-[0.14em] text-gold">Quick Links</p>
            <nav className="mt-5 flex flex-col gap-3" aria-label="Footer">
              {quickLinks.map((item) => (
                <SmartLink key={item.id} to={item.url ?? "#"} className="text-body-sm text-white/70 hover:text-white">
                  {item.label}
                </SmartLink>
              ))}
            </nav>
          </div>

          <div className="lg:col-span-4 lg:border-l lg:border-white/10 lg:pl-10">
            <p className="text-caption font-semibold uppercase tracking-[0.14em] text-gold">Contact</p>
            <div className="mt-5 flex flex-col gap-3 text-body-sm text-white/70">
              {(settings.contact_address as string) && (
                <span className="flex items-start gap-2.5">
                  <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0 text-white/40" />
                  {settings.contact_address as string}
                </span>
              )}
              {(settings.contact_phone as string) && (
                <a href={`tel:${settings.contact_phone}`} className="flex items-center gap-2.5 hover:text-white">
                  <Phone className="h-4 w-4 flex-shrink-0 text-white/40" />
                  {settings.contact_phone as string}
                </a>
              )}
              {(settings.contact_email as string) && (
                <a href={`mailto:${settings.contact_email}`} className="flex items-center gap-2.5 hover:text-white">
                  <Mail className="h-4 w-4 flex-shrink-0 text-white/40" />
                  {settings.contact_email as string}
                </a>
              )}
            </div>
          </div>
        </div>
      </Container>

      <div className="border-t border-white/10">
        <Container size="wide" className="flex flex-col items-center gap-2 py-5 text-center text-caption text-white/50 sm:flex-row sm:justify-between sm:text-left">
          <span>{copyright}</span>
        </Container>
      </div>
    </footer>
  );
}
