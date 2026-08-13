import { lazy, Suspense } from "react";
import { Routes, Route } from "react-router-dom";
import { ProtectedRoute } from "./ProtectedRoute";
import { Spinner } from "@/components/ui";

// The layout shells are lazy too, not just the pages inside them — an
// anonymous public visitor has no reason to download AdminLayout's
// Sidebar (every admin module's nav entry + icon) any more than an
// admin session needs PublicLayout's SiteHeader/SiteFooter.
const AdminLayout = lazy(() => import("@/layouts/AdminLayout").then((m) => ({ default: m.AdminLayout })));
const PublicLayout = lazy(() => import("@/layouts/PublicLayout").then((m) => ({ default: m.PublicLayout })));

const Login = lazy(() => import("@/pages/auth/Login").then((m) => ({ default: m.Login })));
const Dashboard = lazy(() => import("@/pages/Dashboard").then((m) => ({ default: m.Dashboard })));
const Settings = lazy(() => import("@/pages/settings/Settings").then((m) => ({ default: m.Settings })));
const MediaLibrary = lazy(() => import("@/pages/media/MediaLibrary").then((m) => ({ default: m.MediaLibrary })));
const Menus = lazy(() => import("@/pages/menus/Menus").then((m) => ({ default: m.Menus })));
const MenuBuilder = lazy(() => import("@/pages/menus/MenuBuilder").then((m) => ({ default: m.MenuBuilder })));
const Pages = lazy(() => import("@/pages/pages/Pages").then((m) => ({ default: m.Pages })));
const PageBuilder = lazy(() => import("@/pages/pages/PageBuilder").then((m) => ({ default: m.PageBuilder })));
const Homepage = lazy(() => import("@/pages/homepage/Homepage").then((m) => ({ default: m.Homepage })));
const HeroSlides = lazy(() => import("@/pages/hero-slides/HeroSlides").then((m) => ({ default: m.HeroSlides })));
const Testimonials = lazy(() => import("@/pages/testimonials/Testimonials").then((m) => ({ default: m.Testimonials })));
const PartnerCategories = lazy(() => import("@/pages/partner-categories/PartnerCategories").then((m) => ({ default: m.PartnerCategories })));
const Partners = lazy(() => import("@/pages/partners/Partners").then((m) => ({ default: m.Partners })));
const Faculties = lazy(() => import("@/pages/faculties/Faculties").then((m) => ({ default: m.Faculties })));
const FacultyEditor = lazy(() => import("@/pages/faculties/FacultyEditor").then((m) => ({ default: m.FacultyEditor })));
const Departments = lazy(() => import("@/pages/departments/Departments").then((m) => ({ default: m.Departments })));
const DepartmentEditor = lazy(() => import("@/pages/departments/DepartmentEditor").then((m) => ({ default: m.DepartmentEditor })));
const CourseLevels = lazy(() => import("@/pages/course-lookups/CourseLevels").then((m) => ({ default: m.CourseLevels })));
const CourseModes = lazy(() => import("@/pages/course-lookups/CourseModes").then((m) => ({ default: m.CourseModes })));
const CourseCategories = lazy(() => import("@/pages/course-categories/CourseCategories").then((m) => ({ default: m.CourseCategories })));
const CourseCategoryEditor = lazy(() => import("@/pages/course-categories/CourseCategoryEditor").then((m) => ({ default: m.CourseCategoryEditor })));
const Courses = lazy(() => import("@/pages/courses/Courses").then((m) => ({ default: m.Courses })));
const CourseEditor = lazy(() => import("@/pages/courses/CourseEditor").then((m) => ({ default: m.CourseEditor })));
const BlogCategories = lazy(() => import("@/pages/blog-categories/BlogCategories").then((m) => ({ default: m.BlogCategories })));
const Tags = lazy(() => import("@/pages/tags/Tags").then((m) => ({ default: m.Tags })));
const BlogPosts = lazy(() => import("@/pages/blog/BlogPosts").then((m) => ({ default: m.BlogPosts })));
const BlogPostEditor = lazy(() => import("@/pages/blog/BlogPostEditor").then((m) => ({ default: m.BlogPostEditor })));
const NewsCategories = lazy(() => import("@/pages/news-categories/NewsCategories").then((m) => ({ default: m.NewsCategories })));
const NewsList = lazy(() => import("@/pages/news/NewsList").then((m) => ({ default: m.NewsList })));
const NewsEditor = lazy(() => import("@/pages/news/NewsEditor").then((m) => ({ default: m.NewsEditor })));
const Events = lazy(() => import("@/pages/events/Events").then((m) => ({ default: m.Events })));
const EventEditor = lazy(() => import("@/pages/events/EventEditor").then((m) => ({ default: m.EventEditor })));
const GalleryAlbums = lazy(() => import("@/pages/gallery-albums/GalleryAlbums").then((m) => ({ default: m.GalleryAlbums })));
const GalleryAlbumEditor = lazy(() => import("@/pages/gallery-albums/GalleryAlbumEditor").then((m) => ({ default: m.GalleryAlbumEditor })));
const FaqCategories = lazy(() => import("@/pages/faq-categories/FaqCategories").then((m) => ({ default: m.FaqCategories })));
const Faq = lazy(() => import("@/pages/faq/Faq").then((m) => ({ default: m.Faq })));
const DownloadCategories = lazy(() => import("@/pages/download-categories/DownloadCategories").then((m) => ({ default: m.DownloadCategories })));
const Downloads = lazy(() => import("@/pages/downloads/Downloads").then((m) => ({ default: m.Downloads })));
const Applications = lazy(() => import("@/pages/applications/Applications").then((m) => ({ default: m.Applications })));
const ApplicationDetail = lazy(() => import("@/pages/applications/ApplicationDetail").then((m) => ({ default: m.ApplicationDetail })));
const Inquiries = lazy(() => import("@/pages/inquiries/Inquiries").then((m) => ({ default: m.Inquiries })));
const SeoManager = lazy(() => import("@/pages/seo/SeoManager").then((m) => ({ default: m.SeoManager })));
const Users = lazy(() => import("@/pages/users/Users").then((m) => ({ default: m.Users })));
const Roles = lazy(() => import("@/pages/roles/Roles").then((m) => ({ default: m.Roles })));

const Home = lazy(() => import("@/pages/public/Home").then((m) => ({ default: m.Home })));
const StaticPage = lazy(() => import("@/pages/public/StaticPage").then((m) => ({ default: m.StaticPage })));
const PublicFaculties = lazy(() => import("@/pages/public/Faculties").then((m) => ({ default: m.Faculties })));
const FacultyDetail = lazy(() => import("@/pages/public/FacultyDetail").then((m) => ({ default: m.FacultyDetail })));
const PublicDepartments = lazy(() => import("@/pages/public/Departments").then((m) => ({ default: m.Departments })));
const DepartmentDetail = lazy(() => import("@/pages/public/DepartmentDetail").then((m) => ({ default: m.DepartmentDetail })));
const PublicCourses = lazy(() => import("@/pages/public/Courses").then((m) => ({ default: m.Courses })));
const CourseDetail = lazy(() => import("@/pages/public/CourseDetail").then((m) => ({ default: m.CourseDetail })));
const Blog = lazy(() => import("@/pages/public/Blog").then((m) => ({ default: m.Blog })));
const BlogDetail = lazy(() => import("@/pages/public/BlogDetail").then((m) => ({ default: m.BlogDetail })));
const PublicNews = lazy(() => import("@/pages/public/News").then((m) => ({ default: m.News })));
const NewsDetail = lazy(() => import("@/pages/public/NewsDetail").then((m) => ({ default: m.NewsDetail })));
const PublicEvents = lazy(() => import("@/pages/public/Events").then((m) => ({ default: m.Events })));
const EventDetail = lazy(() => import("@/pages/public/EventDetail").then((m) => ({ default: m.EventDetail })));
const Gallery = lazy(() => import("@/pages/public/Gallery").then((m) => ({ default: m.Gallery })));
const GalleryAlbumDetail = lazy(() => import("@/pages/public/GalleryAlbumDetail").then((m) => ({ default: m.GalleryAlbumDetail })));
const Contact = lazy(() => import("@/pages/public/Contact").then((m) => ({ default: m.Contact })));
const PublicFaq = lazy(() => import("@/pages/public/Faq").then((m) => ({ default: m.Faq })));
const About = lazy(() => import("@/pages/public/About").then((m) => ({ default: m.About })));
const Admissions = lazy(() => import("@/pages/public/Admissions").then((m) => ({ default: m.Admissions })));
const HowToApply = lazy(() => import("@/pages/public/HowToApply").then((m) => ({ default: m.HowToApply })));
const InternationalStudents = lazy(() =>
  import("@/pages/public/InternationalStudents").then((m) => ({ default: m.InternationalStudents }))
);
const StudentLife = lazy(() => import("@/pages/public/StudentLife").then((m) => ({ default: m.StudentLife })));
const PrivacyPolicy = lazy(() => import("@/pages/public/PrivacyPolicy").then((m) => ({ default: m.PrivacyPolicy })));
const Terms = lazy(() => import("@/pages/public/Terms").then((m) => ({ default: m.Terms })));
const RefundPolicy = lazy(() => import("@/pages/public/RefundPolicy").then((m) => ({ default: m.RefundPolicy })));
const PublicDownloads = lazy(() => import("@/pages/public/Downloads").then((m) => ({ default: m.Downloads })));
const Apply = lazy(() => import("@/pages/public/Apply").then((m) => ({ default: m.Apply })));
const ApplyResume = lazy(() => import("@/pages/public/ApplyResume").then((m) => ({ default: m.ApplyResume })));
const SearchResults = lazy(() => import("@/pages/public/SearchResults").then((m) => ({ default: m.SearchResults })));
const NotFound = lazy(() => import("@/pages/public/NotFound").then((m) => ({ default: m.NotFound })));

/**
 * Every page component is a separate lazily-loaded chunk (Milestone 25,
 * Performance Optimization) — before this, one JS bundle held every
 * admin AND public page, so an anonymous visitor's first paint was
 * blocked on downloading the entire Course Builder, Page Builder, Media
 * Library, etc. React Router only ever mounts one route at a time, so
 * splitting per-page is a pure win with no behavior change: each
 * `import()` resolves to a Vite-emitted chunk fetched the first time
 * that route is actually visited, then cached by the browser like any
 * other static asset. `Suspense`'s fallback only appears on that first
 * fetch — already-visited routes swap instantly.
 *
 * Route ranking: React Router resolves static segments (`/courses`)
 * ahead of dynamic ones (`/:slug`) regardless of declaration order, so
 * the generic `/:slug` Page Builder catch-all can safely sit last
 * without shadowing any listing route above it.
 */
function RouteFallback() {
  return (
    <div className="flex min-h-[40vh] items-center justify-center">
      <Spinner className="h-8 w-8" />
    </div>
  );
}

export function AppRoutes() {
  return (
    <Suspense fallback={<RouteFallback />}>
      <Routes>
        <Route path="/login" element={<Login />} />

        <Route element={<ProtectedRoute />}>
          <Route path="/admin" element={<AdminLayout />}>
            <Route index element={<Dashboard />} />
            <Route path="settings" element={<Settings />} />
            <Route path="media-library" element={<MediaLibrary />} />
            <Route path="menus" element={<Menus />} />
            <Route path="menus/:id" element={<MenuBuilder />} />
            <Route path="pages" element={<Pages />} />
            <Route path="pages/:id" element={<PageBuilder />} />
            <Route path="homepage" element={<Homepage />} />
            <Route path="hero-slides" element={<HeroSlides />} />
            <Route path="testimonials" element={<Testimonials />} />
            <Route path="partner-categories" element={<PartnerCategories />} />
            <Route path="partners" element={<Partners />} />
            <Route path="faculties" element={<Faculties />} />
            <Route path="faculties/:id" element={<FacultyEditor />} />
            <Route path="departments" element={<Departments />} />
            <Route path="departments/:id" element={<DepartmentEditor />} />
            <Route path="course-levels" element={<CourseLevels />} />
            <Route path="course-modes" element={<CourseModes />} />
            <Route path="course-categories" element={<CourseCategories />} />
            <Route path="course-categories/:id" element={<CourseCategoryEditor />} />
            <Route path="courses" element={<Courses />} />
            <Route path="courses/:id" element={<CourseEditor />} />
            <Route path="blog-categories" element={<BlogCategories />} />
            <Route path="tags" element={<Tags />} />
            <Route path="blog" element={<BlogPosts />} />
            <Route path="blog/:id" element={<BlogPostEditor />} />
            <Route path="news-categories" element={<NewsCategories />} />
            <Route path="news" element={<NewsList />} />
            <Route path="news/:id" element={<NewsEditor />} />
            <Route path="events" element={<Events />} />
            <Route path="events/:id" element={<EventEditor />} />
            <Route path="gallery-albums" element={<GalleryAlbums />} />
            <Route path="gallery-albums/:id" element={<GalleryAlbumEditor />} />
            <Route path="faq-categories" element={<FaqCategories />} />
            <Route path="faqs" element={<Faq />} />
            <Route path="download-categories" element={<DownloadCategories />} />
            <Route path="downloads" element={<Downloads />} />
            <Route path="applications" element={<Applications />} />
            <Route path="applications/:id" element={<ApplicationDetail />} />
            <Route path="inquiries" element={<Inquiries />} />
            <Route path="seo" element={<SeoManager />} />
            <Route path="users" element={<Users />} />
            <Route path="roles" element={<Roles />} />
          </Route>
        </Route>

        <Route element={<PublicLayout />}>
          <Route index element={<Home />} />
          <Route path="faculties" element={<PublicFaculties />} />
          <Route path="faculties/:slug" element={<FacultyDetail />} />
          <Route path="departments" element={<PublicDepartments />} />
          <Route path="departments/:slug" element={<DepartmentDetail />} />
          <Route path="courses" element={<PublicCourses />} />
          <Route path="courses/:slug" element={<CourseDetail />} />
          <Route path="blog" element={<Blog />} />
          <Route path="blog/:slug" element={<BlogDetail />} />
          <Route path="news" element={<PublicNews />} />
          <Route path="news/:slug" element={<NewsDetail />} />
          <Route path="events" element={<PublicEvents />} />
          <Route path="events/:slug" element={<EventDetail />} />
          <Route path="gallery" element={<Gallery />} />
          <Route path="gallery/:slug" element={<GalleryAlbumDetail />} />
          <Route path="faq" element={<PublicFaq />} />
          <Route path="downloads" element={<PublicDownloads />} />
          <Route path="apply" element={<Apply />} />
          <Route path="apply/resume" element={<ApplyResume />} />
          <Route path="search" element={<SearchResults />} />
          <Route path="contact" element={<Contact />} />
          <Route path="about" element={<About />} />
          <Route path="admissions" element={<Admissions />} />
          <Route path="how-to-apply" element={<HowToApply />} />
          <Route path="international-students" element={<InternationalStudents />} />
          <Route path="student-life" element={<StudentLife />} />
          <Route path="privacy-policy" element={<PrivacyPolicy />} />
          <Route path="terms" element={<Terms />} />
          <Route path="refund-policy" element={<RefundPolicy />} />
          <Route path=":slug" element={<StaticPage />} />
          <Route path="*" element={<NotFound />} />
        </Route>
      </Routes>
    </Suspense>
  );
}
