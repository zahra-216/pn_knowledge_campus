/**
 * Every endpoint path used by the app is declared here, once, matching
 * the API Design document exactly. No component or hook should ever
 * write a raw "/api/v1/..." string — import from here instead, so a path
 * change in the API Design document means editing exactly one line.
 *
 * Grouped to match the API Design document's own section numbering
 * (Section 2 — Auth, Section 8 — Admin API). Public API and further Admin
 * API groups are added here module-by-module as each Development
 * Roadmap milestone ships — Milestone 1 adds Settings, Branches, Social
 * Links, and Media Library. Do not pre-add Course/News paths yet.
 */
export const ENDPOINTS = {
  auth: {
    login: "/auth/login",
    logout: "/auth/logout",
    me: "/auth/me",
    forgotPassword: "/auth/forgot-password",
    resetPassword: "/auth/reset-password",
  },
  settings: {
    admin: "/admin/settings",
    public: "/settings/public",
  },
  branches: {
    admin: (id?: number) => (id ? `/admin/branches/${id}` : "/admin/branches"),
    public: "/branches",
  },
  socialLinks: {
    admin: (id?: number) => (id ? `/admin/social-links/${id}` : "/admin/social-links"),
    public: "/social-links",
  },
  media: {
    list: "/admin/media",
    upload: "/admin/media",
    detail: (id: number) => `/admin/media/${id}`,
    replace: (id: number) => `/admin/media/${id}/replace`,
    resolve: (ids: number[]) => `/media/resolve?ids=${ids.join(",")}`,
  },
  mediaFolders: {
    list: "/admin/media-folders",
    create: "/admin/media-folders",
    detail: (id: number) => `/admin/media-folders/${id}`,
  },
  officeHours: {
    admin: "/admin/office-hours",
    public: "/office-hours",
  },
  menus: {
    admin: (id?: number) => (id ? `/admin/menus/${id}` : "/admin/menus"),
    items: (menuId: number, itemId?: number) =>
      itemId ? `/admin/menus/${menuId}/items/${itemId}` : `/admin/menus/${menuId}/items`,
    reorder: (menuId: number) => `/admin/menus/${menuId}/items/reorder`,
    public: (key: string) => `/menus/${key}`,
  },
  pages: {
    admin: (id?: number) => (id ? `/admin/pages/${id}` : "/admin/pages"),
    publish: (id: number) => `/admin/pages/${id}/publish`,
    blocks: (pageId: number, blockId?: number) =>
      blockId ? `/admin/pages/${pageId}/blocks/${blockId}` : `/admin/pages/${pageId}/blocks`,
    reorderBlocks: (pageId: number) => `/admin/pages/${pageId}/blocks/reorder`,
    public: (slug: string) => `/pages/${slug}`,
  },
  homepage: {
    sections: "/admin/homepage-sections",
    reorderSections: "/admin/homepage-sections/reorder",
    content: "/admin/homepage-content",
    public: "/homepage",
  },
  heroSlides: {
    admin: (id?: number) => (id ? `/admin/hero-slides/${id}` : "/admin/hero-slides"),
    public: "/hero-slides",
  },
  testimonials: {
    admin: (id?: number) => (id ? `/admin/testimonials/${id}` : "/admin/testimonials"),
    public: "/testimonials",
  },
  partnerCategories: {
    admin: (id?: number) => (id ? `/admin/partner-categories/${id}` : "/admin/partner-categories"),
    public: "/partner-categories",
  },
  partners: {
    admin: (id?: number) => (id ? `/admin/partners/${id}` : "/admin/partners"),
    public: "/partners",
  },
  faculties: {
    admin: (id?: number) => (id ? `/admin/faculties/${id}` : "/admin/faculties"),
    gallery: (facultyId: number, mediaId?: number) =>
      mediaId ? `/admin/faculties/${facultyId}/gallery/${mediaId}` : `/admin/faculties/${facultyId}/gallery`,
    publicList: "/faculties",
    public: (slug: string) => `/faculties/${slug}`,
  },
  departments: {
    admin: (id?: number) => (id ? `/admin/departments/${id}` : "/admin/departments"),
    publicList: "/departments",
    public: (slug: string) => `/departments/${slug}`,
  },
  courseLevels: {
    admin: (id?: number) => (id ? `/admin/course-levels/${id}` : "/admin/course-levels"),
    public: "/course-levels",
  },
  courseModes: {
    admin: (id?: number) => (id ? `/admin/course-modes/${id}` : "/admin/course-modes"),
    public: "/course-modes",
  },
  courseCategories: {
    admin: (id?: number) => (id ? `/admin/course-categories/${id}` : "/admin/course-categories"),
    reorder: "/admin/course-categories/reorder",
    public: "/course-categories",
  },
  courses: {
    admin: (id?: number) => (id ? `/admin/courses/${id}` : "/admin/courses"),
    publish: (id: number) => `/admin/courses/${id}/publish`,
    media: (id: number) => `/admin/courses/${id}/media`,
    detachMedia: (id: number, mediaId: number) => `/admin/courses/${id}/media/${mediaId}`,
    curriculum: (courseId: number, itemId?: number) =>
      itemId ? `/admin/courses/${courseId}/curriculum/${itemId}` : `/admin/courses/${courseId}/curriculum`,
    reorderCurriculum: (courseId: number) => `/admin/courses/${courseId}/curriculum/reorder`,
    faqs: (courseId: number, faqId?: number) =>
      faqId ? `/admin/courses/${courseId}/faqs/${faqId}` : `/admin/courses/${courseId}/faqs`,
    publicList: "/courses",
    public: (slug: string) => `/courses/${slug}`,
  },
  blogCategories: {
    admin: (id?: number) => (id ? `/admin/blog-categories/${id}` : "/admin/blog-categories"),
    public: "/blog-categories",
  },
  tags: {
    admin: (id?: number) => (id ? `/admin/tags/${id}` : "/admin/tags"),
  },
  blog: {
    admin: (id?: number) => (id ? `/admin/blog/${id}` : "/admin/blog"),
    publish: (id: number) => `/admin/blog/${id}/publish`,
    media: (id: number) => `/admin/blog/${id}/media`,
    detachMedia: (id: number, mediaId: number) => `/admin/blog/${id}/media/${mediaId}`,
    publicList: "/blog",
    public: (slug: string) => `/blog/${slug}`,
  },
  newsCategories: {
    admin: (id?: number) => (id ? `/admin/news-categories/${id}` : "/admin/news-categories"),
    public: "/news-categories",
  },
  news: {
    admin: (id?: number) => (id ? `/admin/news/${id}` : "/admin/news"),
    publish: (id: number) => `/admin/news/${id}/publish`,
    media: (id: number) => `/admin/news/${id}/media`,
    detachMedia: (id: number, mediaId: number) => `/admin/news/${id}/media/${mediaId}`,
    publicList: "/news",
    public: (slug: string) => `/news/${slug}`,
  },
  events: {
    admin: (id?: number) => (id ? `/admin/events/${id}` : "/admin/events"),
    media: (id: number) => `/admin/events/${id}/media`,
    detachMedia: (id: number, mediaId: number) => `/admin/events/${id}/media/${mediaId}`,
    speakers: (eventId: number, speakerId?: number) =>
      speakerId ? `/admin/events/${eventId}/speakers/${speakerId}` : `/admin/events/${eventId}/speakers`,
    publicList: "/events",
    public: (slug: string) => `/events/${slug}`,
  },
  galleryAlbums: {
    admin: (id?: number) => (id ? `/admin/gallery-albums/${id}` : "/admin/gallery-albums"),
    media: (id: number) => `/admin/gallery-albums/${id}/media`,
    mediaItem: (id: number, mediaId: number) => `/admin/gallery-albums/${id}/media/${mediaId}`,
    publicList: "/gallery-albums",
    public: (slug: string) => `/gallery-albums/${slug}`,
  },
  faqCategories: {
    admin: (id?: number) => (id ? `/admin/faq-categories/${id}` : "/admin/faq-categories"),
    public: "/faq-categories",
  },
  faqs: {
    admin: (id?: number) => (id ? `/admin/faqs/${id}` : "/admin/faqs"),
    publicList: "/faqs",
  },
  downloadCategories: {
    admin: (id?: number) => (id ? `/admin/download-categories/${id}` : "/admin/download-categories"),
    public: "/download-categories",
  },
  downloads: {
    admin: (id?: number) => (id ? `/admin/downloads/${id}` : "/admin/downloads"),
    publicList: "/downloads",
  },
  seo: {
    admin: (type: string, id: number) => `/admin/seo/${type}/${id}`,
    summary: "/admin/seo",
    typeList: (type: string) => `/admin/seo/${type}`,
  },
  inquiries: {
    submit: "/inquiries",
    adminList: "/admin/inquiries",
    adminExport: "/admin/inquiries/export",
    adminShow: (id: number) => `/admin/inquiries/${id}`,
    adminStatus: (id: number) => `/admin/inquiries/${id}/status`,
  },
  users: {
    admin: (id?: number) => (id ? `/admin/users/${id}` : "/admin/users"),
  },
  roles: {
    admin: (id?: number) => (id ? `/admin/roles/${id}` : "/admin/roles"),
    permissions: "/admin/permissions",
  },
  applications: {
    // Visitor-facing (public, no auth) — bound by application_number.
    create: "/applications",
    lookup: "/applications/lookup",
    update: (applicationNumber: string) => `/applications/${applicationNumber}`,
    documents: (applicationNumber: string) => `/applications/${applicationNumber}/documents`,
    document: (applicationNumber: string, documentId: number) => `/applications/${applicationNumber}/documents/${documentId}`,
    submit: (applicationNumber: string) => `/applications/${applicationNumber}/submit`,
    // Admin (authenticated, applications.* gated) — bound by numeric id.
    adminList: "/admin/applications",
    adminExport: "/admin/applications/export",
    adminShow: (id: number) => `/admin/applications/${id}`,
    adminUnderReview: (id: number) => `/admin/applications/${id}/under-review`,
    adminApprove: (id: number) => `/admin/applications/${id}/approve`,
    adminReject: (id: number) => `/admin/applications/${id}/reject`,
  },
  search: {
    results: (q: string, types?: string[]) => `/search?q=${encodeURIComponent(q)}${types?.length ? `&type=${types.join(",")}` : ""}`,
    autocomplete: (q: string) => `/search/autocomplete?q=${encodeURIComponent(q)}`,
  },
  notifications: {
    list: "/admin/notifications",
    markRead: (id: string) => `/admin/notifications/${id}/read`,
    markAllRead: "/admin/notifications/read-all",
  },
  analytics: {
    pageview: "/analytics/pageview",
    dashboard: (days: number) => `/admin/analytics/dashboard?days=${days}`,
  },
} as const;
