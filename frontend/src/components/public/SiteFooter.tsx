import { Link } from "react-router-dom";
import { Mail, Phone, MapPin, Facebook, Twitter, Instagram, Linkedin, Youtube, Globe } from "lucide-react";
import type { LucideIcon } from "lucide-react";
import { usePublicDetail } from "@/hooks/usePublicDetail";
import { usePublicSettings } from "@/hooks/usePublicSettings";
import { ENDPOINTS } from "@/lib/endpoints";
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
 * Settings + Menu driven footer — quick links come from the same
 * `footer` Menu the admin Menu Builder edits, contact details/social
 * links/copyright come from Settings/SocialLinks, none of it hardcoded.
 */
export function SiteFooter() {
  const { data: menu } = usePublicDetail<Menu>(ENDPOINTS.menus.public("footer"));
  const { data: socialLinks } = usePublicDetail<SocialLink[]>(ENDPOINTS.socialLinks.public);
  const { settings } = usePublicSettings();

  const campusName = (settings.campus_short_name as string) || (settings.campus_name as string) || "PN Knowledge Campus";
  const tagline = settings.campus_tagline as string | undefined;
  const year = new Date().getFullYear();
  const copyright = (settings.footer_copyright as string) || `© ${year} ${campusName}. All rights reserved.`;

  return (
    <footer className="border-t border-[color:var(--color-border)] bg-navy text-white">
      <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div className="lg:col-span-2">
          <p className="font-display text-h4 font-semibold">{campusName}</p>
          {tagline && <p className="mt-2 max-w-sm text-body-sm text-white/70">{tagline}</p>}
          {(settings.footer_text as string) && <p className="mt-4 max-w-sm text-body-sm text-white/70">{settings.footer_text as string}</p>}

          {socialLinks && socialLinks.length > 0 && (
            <div className="mt-6 flex gap-3">
              {socialLinks.map((link) => {
                const Icon = PLATFORM_ICON[link.platform.toLowerCase()] ?? Globe;
                return (
                  <a
                    key={link.id}
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={link.platform}
                    className="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 hover:bg-gold"
                  >
                    <Icon className="h-4 w-4" />
                  </a>
                );
              })}
            </div>
          )}
        </div>

        <div>
          <p className="text-body-sm font-semibold uppercase tracking-wide text-gold">Quick Links</p>
          <nav className="mt-4 flex flex-col gap-2" aria-label="Footer">
            {(menu?.items ?? []).map((item) => (
              <Link key={item.id} to={item.url ?? "#"} className="text-body-sm text-white/70 hover:text-white">
                {item.label}
              </Link>
            ))}
          </nav>
        </div>

        <div>
          <p className="text-body-sm font-semibold uppercase tracking-wide text-gold">Contact</p>
          <div className="mt-4 flex flex-col gap-3 text-body-sm text-white/70">
            {(settings.contact_address as string) && (
              <span className="flex items-start gap-2">
                <MapPin className="mt-0.5 h-4 w-4 flex-shrink-0" />
                {settings.contact_address as string}
              </span>
            )}
            {(settings.contact_phone as string) && (
              <a href={`tel:${settings.contact_phone}`} className="flex items-center gap-2 hover:text-white">
                <Phone className="h-4 w-4 flex-shrink-0" />
                {settings.contact_phone as string}
              </a>
            )}
            {(settings.contact_email as string) && (
              <a href={`mailto:${settings.contact_email}`} className="flex items-center gap-2 hover:text-white">
                <Mail className="h-4 w-4 flex-shrink-0" />
                {settings.contact_email as string}
              </a>
            )}
          </div>
        </div>
      </div>

      <div className="border-t border-white/10 px-4 py-5 text-center text-caption text-white/60 sm:px-6 lg:px-8">{copyright}</div>
    </footer>
  );
}
