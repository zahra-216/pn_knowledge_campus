/** Matches SocialLinkResource on the backend (Database Design, Section 4.2). */
export interface SocialLink {
  id: number;
  platform: string;
  url: string;
  order: number;
  is_active: boolean;
}

export type SocialLinkPayload = Partial<Pick<SocialLink, "platform" | "url" | "order" | "is_active">>;
