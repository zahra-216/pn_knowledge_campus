<?php

namespace Database\Seeders\Demo;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Download;
use App\Models\DownloadAttachment;
use App\Models\DownloadCategory;
use App\Models\Event;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Partner;
use App\Models\PartnerCategory;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\Demo\Concerns\SeedsMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Content Population pass (demo data) — expands Blog/News/Events/
 * Gallery/Testimonials/Partners/FAQs/Downloads from their seed-data
 * starting point to a realistic evaluation catalog, and attaches real
 * images/files to everything (including the pre-existing rows, which
 * shipped with no media by design — see e.g. BlogPostSeeder's docblock).
 */
class DemoPublishingSeeder extends Seeder
{
    use SeedsMedia;

    private DemoImageLibrary $images;

    public function run(): void
    {
        $this->images = new DemoImageLibrary;

        $this->seedBlog();
        $this->seedNews();
        $this->seedEvents();
        $this->seedGalleryAlbums();
        $this->seedTestimonials();
        $this->seedPartners();
        $this->seedFaqs();
        $this->seedDownloads();
    }

    // ---------------------------------------------------------------
    // Blog
    // ---------------------------------------------------------------

    private function seedBlog(): void
    {
        BlogCategory::firstOrCreate(['slug' => 'faculty-insights'], ['name' => 'Faculty Insights', 'order' => 3]);

        foreach (['Career', 'International'] as $name) {
            Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $author = User::where('email', 'superadmin@pnknowledgecampus.edu')->first();

        // Attach images to the 3 posts BlogPostSeeder already created.
        $this->enrichBlogPost('welcome-to-the-new-academic-year', 'campus_courtyard');
        $this->enrichBlogPost('five-scholarships-every-new-student-should-know-about', 'student_presentation');
        $this->enrichBlogPost('where-our-graduates-are-now', 'job_interview');

        $this->createBlogPost('Inside Our New Computer Science Lab', 'Campus Life', ['Research'], $author, 'tech_lab_people',
            'Our newest Computer Science lab opened this term with 40 workstations, a dedicated GPU cluster for machine learning coursework, and a 24-hour access policy for final-year project students.',
            false);

        $this->createBlogPost('A Day in the Life of a Nursing Student', 'Student Stories', ['Admissions'], $author, 'nursing_student',
            'From a 6am ward handover to an evening study group, we followed second-year Nursing student Fatima through a typical clinical placement day at one of our partner hospitals.',
            true);

        $this->createBlogPost('How Our Alumni Network Opens Doors', 'Alumni Spotlight', ['Career'], $author, 'team_handshake',
            'More than 60% of our graduating class finds their first role through a referral from our 4,000-strong alumni network — here is how it actually works, in three graduates\' own words.',
            false);

        $this->createBlogPost("Faculty of Engineering's Latest Bridge-Monitoring Research", 'Faculty Insights', ['Research'], $author, 'civil_engineering_site',
            'A team of final-year Civil Engineering students, supervised by faculty, has deployed low-cost structural sensors on a local pedestrian bridge as part of an ongoing infrastructure-monitoring research project.',
            false);

        $this->createBlogPost('Top Tips for International Students Settling In', 'Campus Life', ['International'], $author, 'international_flags',
            'From opening a local bank account to finding halal, kosher, or vegetarian food on campus, our International Office share the five things every new international student asks about first.',
            false);

        $this->createBlogPost('Behind the Scenes: Our Annual Cultural Festival', 'Campus Life', ['Events'], $author, 'cultural_festival',
            "Founders' Day brings food stalls, performances, and traditions from over 20 countries to the main quad every year — here's a look at how the Student Union pulls it together.",
            false);
    }

    private function enrichBlogPost(string $slug, string $imageKey): void
    {
        $post = BlogPost::where('slug', $slug)->first();

        if ($post) {
            $this->attachImage($post, 'featured_image', $this->images, $imageKey, $post->title);
        }
    }

    /**
     * Uses firstOrCreate (not "return early if it exists") so that
     * re-running this seeder after a media reset (e.g. swapping the
     * entire demo photo set) still re-attaches an image/SEO row even
     * though the BlogPost record itself already exists — attachImage()
     * and the seoMeta()->exists() check below are what keep this
     * idempotent, not skipping the whole function.
     */
    private function createBlogPost(string $title, string $categoryName, array $tagNames, ?User $author, string $imageKey, string $excerpt, bool $isFeatured): void
    {
        $slug = Str::slug($title);
        $category = BlogCategory::where('name', $categoryName)->first();

        $post = BlogPost::firstOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $category?->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => "<p>{$excerpt}</p><p>".$this->paragraph($title).'</p>',
                'author_id' => $author?->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(random_int(1, 60)),
                'is_featured' => $isFeatured,
            ]
        );

        $post->tags()->sync(Tag::whereIn('name', $tagNames)->pluck('id'));
        $this->attachImage($post, 'featured_image', $this->images, $imageKey, $title);

        if (! $post->seoMeta()->exists()) {
            $post->seoMeta()->create([
                'seo_title' => "{$title} | PNK Global Campus Blog",
                'meta_description' => Str::limit($excerpt, 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // News
    // ---------------------------------------------------------------

    private function seedNews(): void
    {
        $author = User::where('email', 'superadmin@pnknowledgecampus.edu')->first();

        $this->enrichNewsArticle('campus-wins-national-innovation-award', 'event_stage_speaker');
        $this->enrichNewsArticle('new-partnership-with-regional-hospitals-announced', 'medical_team');
        $this->enrichNewsArticle('faculty-of-engineering-students-place-first-at-robotics-competition', 'robotics_tech');

        $this->createNewsArticle('PNK Global Campus Launches New Faculty of Health Sciences', 'Announcements', $author, 'medical_students', true,
            'The Faculty will open with Nursing, Public Health, and Medical Laboratory Science programmes, backed by new clinical placement partnerships with three regional hospitals.');

        $this->createNewsArticle('Annual Career Fair Draws Record Number of Employers', 'Achievements', $author, 'career_fair', false,
            'Over 60 employers attended this year\'s Career Fair, up from 38 last year, with on-the-spot interview offers extended to more than 200 students.');

        $this->createNewsArticle('Campus Receives International Accreditation Renewal', 'Press Releases', $author, 'campus_building', false,
            'PNK Global Campus\'s accreditation has been renewed for a further five years following a comprehensive institutional review.');

        $this->createNewsArticle('Student Startup Wins National Business Pitch Competition', 'Achievements', $author, 'business_discussion', false,
            'A team of three Business Administration students took first place — and a demo seed-funding prize — at this year\'s National Student Business Pitch Competition.');

        $this->createNewsArticle('New Scholarship Fund Established for Underprivileged Students', 'Announcements', $author, 'student_presentation', false,
            'A new needs-based scholarship fund will cover full tuition for up to 15 incoming students each year, funded by alumni and corporate donors.');

        $this->createNewsArticle('Campus to Host Regional Cybersecurity Summit', 'Press Releases', $author, 'cybersecurity_lock', false,
            'PNK Global Campus will host next year\'s Regional Cybersecurity Summit, bringing together industry practitioners, students, and researchers for a two-day conference.');
    }

    private function enrichNewsArticle(string $slug, string $imageKey): void
    {
        $article = News::where('slug', $slug)->first();

        if ($article) {
            $this->attachImage($article, 'featured_image', $this->images, $imageKey, $article->title);
        }
    }

    private function createNewsArticle(string $title, string $categoryName, ?User $author, string $imageKey, bool $isFeatured, string $excerpt): void
    {
        $slug = Str::slug($title);
        $category = NewsCategory::where('name', $categoryName)->first();

        $article = News::firstOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $category?->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => "<p>{$excerpt}</p><p>".$this->paragraph($title).'</p>',
                'author_id' => $author?->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(random_int(1, 45)),
                'is_featured' => $isFeatured,
            ]
        );

        $this->attachImage($article, 'featured_image', $this->images, $imageKey, $title);

        if (! $article->seoMeta()->exists()) {
            $article->seoMeta()->create([
                'seo_title' => "{$title} | PNK Global Campus News",
                'meta_description' => Str::limit($excerpt, 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // Events
    // ---------------------------------------------------------------

    private function seedEvents(): void
    {
        $this->enrichEvent('open-day-2026', 'campus_courtyard', 'https://forms.example.test/open-day-2026-rsvp');
        $this->enrichEvent('annual-tech-symposium', 'conference_audience', 'https://forms.example.test/tech-symposium-rsvp');
        $this->enrichEvent('graduation-ceremony-2025', 'graduation_ceremony', null);

        $careerFair = $this->createEvent(
            'Career Fair 2026', 'Main Campus Sports Hall', Carbon::now()->addWeeks(6), Carbon::now()->addWeeks(6)->addHours(5),
            false, 'career_fair', 'https://forms.example.test/career-fair-2026-rsvp',
            'Meet over 50 employers from technology, engineering, healthcare, and finance at our largest careers event of the year — open to all students and recent graduates.'
        );
        $careerFair?->speakers()->firstOrCreate(['name' => 'Ranjit Kumara'], ['title' => 'Head of Talent Acquisition, Regional Employers Consortium', 'order' => 0]);

        $welcomeWeek = $this->createEvent(
            'International Students Welcome Week', 'Main Campus Auditorium', Carbon::now()->addWeeks(3), Carbon::now()->addWeeks(3)->addDays(4),
            false, 'international_flags', 'https://forms.example.test/welcome-week-rsvp',
            'A week of orientation sessions, visa and housing clinics, and a welcome dinner for every new international student joining us this term.'
        );
        $welcomeWeek?->speakers()->firstOrCreate(['name' => 'Dr. Amara Silva'], ['title' => 'Head, International Office', 'order' => 0]);

        $cyberSummit = $this->createEvent(
            'Cybersecurity Summit 2025', 'Grand Hall', Carbon::now()->subMonths(4), Carbon::now()->subMonths(4)->addHours(7),
            false, 'cybersecurity_lock', null,
            'Industry practitioners and researchers gathered for a two-day summit on network defence, ethical hacking, and digital forensics.'
        );
        $cyberSummit?->speakers()->firstOrCreate(['name' => 'Nadia Ibrahim'], ['title' => 'Chief Information Security Officer, TechCorp Solutions', 'order' => 0]);

        $this->createEvent(
            "Founders' Day Cultural Festival", 'Main Quad', Carbon::now()->subMonths(6), Carbon::now()->subMonths(6)->addHours(8),
            false, 'cultural_festival', null,
            'Our annual celebration of the cultures represented across our student body, with food stalls, performances, and traditions from over 20 countries.'
        );
    }

    private function enrichEvent(string $slug, string $imageKey, ?string $registrationUrl): void
    {
        $event = Event::where('slug', $slug)->first();

        if (! $event) {
            return;
        }

        $this->attachImage($event, 'featured_image', $this->images, $imageKey, $event->title);

        if ($registrationUrl && ! $event->registration_url) {
            $event->update(['registration_url' => $registrationUrl]);
        }
    }

    private function createEvent(string $title, ?string $venue, Carbon $startsAt, ?Carbon $endsAt, bool $isOnline, string $imageKey, ?string $registrationUrl, string $description): ?Event
    {
        $slug = Str::slug($title);

        $event = Event::firstOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'venue' => $venue,
                'is_online' => $isOnline,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                // Audit fix (Medium remediation) — Event.description is a
                // plain <Textarea> field (EventDetailsTab.tsx), not rich
                // text; the <p> wrapper showed up as literal, visible tags
                // both on the public event page and in its meta description.
                'description' => $description,
                'registration_url' => $registrationUrl,
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $this->attachImage($event, 'featured_image', $this->images, $imageKey, $title);

        if (! $event->seoMeta()->exists()) {
            $event->seoMeta()->create([
                'seo_title' => "{$title} | PNK Global Campus Events",
                'meta_description' => Str::limit($description, 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }

        return $event;
    }

    // ---------------------------------------------------------------
    // Gallery
    // ---------------------------------------------------------------

    private function seedGalleryAlbums(): void
    {
        $this->fillAlbum('open-day-2026', [
            ['event_audience', 'Prospective students and families arriving for Open Day 2026'],
            ['event_stage_speaker', 'The Dean of Admissions welcomes attendees'],
            ['campus_courtyard', 'Campus tours departing from the main courtyard'],
            ['team_handshake', 'A faculty advisor meets with a prospective student'],
            ['campus_aerial', 'An aerial view of the main campus taken during Open Day'],
            ['students_walking', 'Current students volunteering as campus tour guides'],
        ]);

        $this->fillAlbum('campus-life', [
            ['library_study', 'Students studying together in the main library'],
            ['dorm_room', 'A student dormitory room in the campus residence halls'],
            ['sports_field', 'An intramural football match on the campus sports field'],
            ['cultural_festival', "Performers at last year's Founders' Day festival"],
            ['student_portrait_2', 'A student between classes on the main quad'],
            ['diverse_students_group', 'Students from our International Society at a weekly meetup'],
        ]);

        $this->fillAlbum('graduation-ceremony-2025', [
            ['graduation_ceremony', 'Graduates during the 2025 conferral ceremony'],
            ['graduates_walking', 'Graduates processing into the Grand Hall'],
            ['campus_courtyard', 'Families gathering for photos after the ceremony'],
            ['student_portrait_3', 'A graduate of the Faculty of Business, Class of 2025'],
            ['campus_building', 'The Grand Hall, venue for the 2025 ceremony'],
            ['event_audience', 'Guests seated for the conferral ceremony'],
        ]);

        $album = GalleryAlbum::firstOrCreate(
            ['slug' => 'international-students-week'],
            ['title' => 'International Students Week', 'order' => 3, 'is_active' => true]
        );
        $this->fillAlbumItems($album, [
            ['international_flags', 'Flags representing the 20+ countries in our student body'],
            ['diverse_students_group', 'New international students at the Welcome Week orientation'],
            ['dorm_room', 'A tour of the international student residence halls'],
            ['student_portrait_4', 'A first-year international student at orientation'],
            ['campus_courtyard', 'The Welcome Week information fair on the main quad'],
            ['job_interview', 'A visa and employment rights briefing session'],
        ]);
    }

    private function fillAlbum(string $slug, array $items): void
    {
        $album = GalleryAlbum::where('slug', $slug)->first();

        if ($album) {
            $this->fillAlbumItems($album, $items);
        }
    }

    private function fillAlbumItems(GalleryAlbum $album, array $items): void
    {
        if ($album->getMedia('items')->isNotEmpty()) {
            return;
        }

        foreach ($items as [$imageKey, $caption]) {
            $media = $album->addMedia($this->images->path($imageKey))
                ->preservingOriginal()
                ->usingName($this->images->label($imageKey))
                ->withCustomProperties(['caption' => $caption])
                ->toMediaCollection('items');

            $media->forceFill(['alt_text' => $caption])->save();
        }
    }

    // ---------------------------------------------------------------
    // Testimonials
    // ---------------------------------------------------------------

    private function seedTestimonials(): void
    {
        $this->enrichTestimonial('Aisha Rahman', 'student_portrait_1');
        $this->enrichTestimonial('Daniel Osei', 'student_portrait_2');
        $this->enrichTestimonial('Priya Nair', 'student_portrait_3');

        $this->createTestimonial('Michael Adeyemi', 'BEng Civil Engineering Graduate, 2023', 'ENG-BEN-001', 'student_portrait_4', 5,
            'The capstone project put me on a real construction site with real deadlines — I walked into my first job already knowing how a site actually runs.', true);

        $this->createTestimonial('Fatima Al-Sayed', 'International Student, BSc Nursing', 'HS-BSC-001', 'portrait_5', 5,
            'I was nervous about starting clinical placements so early, but the supervision here is genuinely hands-on. I never felt thrown in the deep end.', true);

        $this->createTestimonial('Kwame Boateng', 'MBA Graduate, 2024', 'BUS-MBA-001', 'portrait_6', 5,
            'Studying alongside people already running their own teams changed how I think about leadership. The monthly residencies alone were worth the tuition.', true);

        $this->createTestimonial('Sarah Chen', 'BSc Data Science, Current Student', 'CS-BSC-002', 'portrait_7', 4,
            "I came in with zero programming background and by second year I was building real models. The lecturers actually notice if you're falling behind.", false);

        $this->createTestimonial('James Okafor', 'Diploma in Accounting & Finance Graduate, 2022', 'BUS-DIP-001', 'portrait_8', 4,
            'Studying part-time while working full-time was hard, but the evening schedule and supportive lecturers made it genuinely doable.', false);

        $this->createTestimonial('Layla Hassan', 'BSc Cybersecurity, Current Student', 'CS-BSC-004', 'portrait_9', 5,
            'Our own dedicated penetration-testing lab is not something I expected from a university this size — it feels like training for the actual job.', false);
    }

    private function enrichTestimonial(string $name, string $imageKey): void
    {
        $testimonial = Testimonial::where('name', $name)->first();

        if ($testimonial) {
            $this->attachImage($testimonial, 'photo', $this->images, $imageKey, "Photo of {$name}");
        }
    }

    private function createTestimonial(string $name, string $roleTitle, ?string $courseCode, string $imageKey, int $rating, string $content, bool $isFeatured): void
    {
        $course = $courseCode ? Course::where('course_code', $courseCode)->first() : null;

        $testimonial = Testimonial::firstOrCreate(
            ['name' => $name],
            [
                'role_title' => $roleTitle,
                'course_id' => $course?->id,
                'content' => $content,
                'rating' => $rating,
                'is_featured' => $isFeatured,
                'is_active' => true,
                'order' => Testimonial::count(),
            ]
        );

        $this->attachImage($testimonial, 'photo', $this->images, $imageKey, "Photo of {$name}");
    }

    // ---------------------------------------------------------------
    // Partners
    // ---------------------------------------------------------------

    private function seedPartners(): void
    {
        $this->enrichPartnerLogo('National Accreditation Board', 'nab', 'NAB', '#1B2A4A');
        $this->enrichPartnerLogo('Ministry of Education', 'moe', 'MoE', '#0F1830');
        $this->enrichPartnerLogo('International Education Alliance', 'iea', 'IEA', '#2A5DAB');
        $this->enrichPartnerLogo('TechCorp Solutions', 'techcorp', 'TCS', '#1B6B2E');

        $accreditationBody = PartnerCategory::where('slug', 'accreditation-body')->first();
        $academicPartner = PartnerCategory::where('slug', 'academic-partner')->first();
        $industryPartner = PartnerCategory::where('slug', 'industry-partner')->first();

        $this->createPartner('Global Health Partners Network', $accreditationBody?->id, 'https://example.test/global-health-partners', 'ghpn', 'GHPN', '#8A6D00');
        $this->createPartner('Regional Employers Consortium', $industryPartner?->id, 'https://example.test/regional-employers', 'rec', 'REC', '#B3261E');
        $this->createPartner('National Nursing & Midwifery Council', $accreditationBody?->id, 'https://example.test/nursing-council', 'nnmc', 'NNMC', '#A6812C');
        $this->createPartner('Innovate Africa Tech Hub', $industryPartner?->id ?? $academicPartner?->id, 'https://example.test/innovate-africa', 'iath', 'IATH', '#2A3F6B');
    }

    private function enrichPartnerLogo(string $name, string $slug, string $label, string $hex): void
    {
        $partner = Partner::where('name', $name)->first();

        if ($partner) {
            $this->attachLogo($partner, 'logo', $this->images, $slug, $label, $hex, "{$name} logo");
        }
    }

    private function createPartner(string $name, ?int $categoryId, string $url, string $slug, string $label, string $hex): void
    {
        $partner = Partner::firstOrCreate(
            ['name' => $name],
            ['category_id' => $categoryId, 'url' => $url, 'order' => Partner::count(), 'is_active' => true]
        );

        $this->attachLogo($partner, 'logo', $this->images, $slug, $label, $hex, "{$name} logo");
    }

    // ---------------------------------------------------------------
    // FAQs
    // ---------------------------------------------------------------

    private function seedFaqs(): void
    {
        FaqCategory::firstOrCreate(['name' => 'Courses & Academics'], ['slug' => 'courses-academics', 'order' => 3]);
        FaqCategory::firstOrCreate(['name' => 'International Students'], ['slug' => 'international-students', 'order' => 4]);

        $admissions = FaqCategory::where('slug', 'admissions')->first();
        $fees = FaqCategory::where('slug', 'fees-scholarships')->first();
        $campusLife = FaqCategory::where('slug', 'campus-life')->first();
        $courses = FaqCategory::where('slug', 'courses-academics')->first();
        $international = FaqCategory::where('slug', 'international-students')->first();

        $this->createFaq('How long does the application process take?', $admissions?->id,
            'Most applications are reviewed within 10 working days of receiving all required documents.');
        $this->createFaq('Can I transfer credits from another institution?', $admissions?->id,
            'Yes — credit transfer is assessed case by case by the relevant Faculty; submit your transcript alongside your application for review.');
        $this->createFaq('What payment plans are available for tuition?', $fees?->id,
            'Tuition can be paid in full, per-semester, or via an approved monthly instalment plan arranged with the Finance Office.');
        $this->createFaq('Is on-campus accommodation available?', $campusLife?->id,
            'Yes — a limited number of on-campus dormitory rooms are available, allocated on a first-come, first-served basis each intake.');
        $this->createFaq('Do you offer part-time or online study options?', $courses?->id,
            'Several programmes, including our MBA and Accounting & Finance diploma, are offered part-time, blended, or fully online.');
        $this->createFaq('What support services are available for international students?', $international?->id,
            'Our International Office supports visa applications, airport pickup, housing, and a dedicated orientation week each intake.');
        $this->createFaq('How do I apply for a student visa?', $international?->id,
            'Once you receive your offer letter, our International Office will provide the documentation and guidance needed for your visa application.');
        $this->createFaq('What career support does the campus provide?', $campusLife?->id,
            'The Careers Office offers CV reviews, mock interviews, and hosts an annual Career Fair with over 50 employers.');
        $this->createFaq('Are there opportunities for work placements or internships?', $courses?->id,
            'Many programmes include an optional or required industry placement — check the individual course page for specifics.');
    }

    private function createFaq(string $question, ?int $categoryId, string $answer): void
    {
        Faq::firstOrCreate(
            ['question' => $question],
            ['category_id' => $categoryId, 'answer' => $answer, 'order' => Faq::global()->count(), 'is_active' => true]
        );
    }

    // ---------------------------------------------------------------
    // Downloads
    // ---------------------------------------------------------------

    private function seedDownloads(): void
    {
        $this->enrichDownload('Undergraduate Prospectus 2026', 'prospectus-undergraduate-2026',
            ['PNK Global Campus', 'Undergraduate Prospectus 2026', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $this->enrichDownload('Application Form', 'application-form',
            ['PNK Global Campus', 'Undergraduate & Postgraduate Application Form', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $this->enrichGatedDownload('Scholarship Application Form', 'scholarship-application-form',
            ['PNK Global Campus', 'Scholarship Application Form', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $this->enrichDownload('Faculty of Computing Brochure', 'faculty-of-computing-brochure',
            ['PNK Global Campus', 'Faculty of Computing Brochure', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $prospectus = DownloadCategory::where('slug', 'prospectus')->first();
        $brochures = DownloadCategory::where('slug', 'brochures')->first();
        $pdfs = DownloadCategory::where('slug', 'pdfs')->first();

        $this->createDownload('Postgraduate Prospectus 2026', $prospectus?->id,
            'A complete guide to our postgraduate and MBA programmes, entry requirements, and application deadlines.',
            'postgraduate-prospectus-2026', ['PNK Global Campus', 'Postgraduate Prospectus 2026', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $this->createDownload('Campus Map & Facilities Guide', $brochures?->id,
            'A printable map of the main campus, including faculty buildings, the library, and student services.',
            'campus-map-facilities-guide', ['PNK Global Campus', 'Campus Map & Facilities Guide', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $feeStructure = $this->createDownload('Fee Structure 2026', $pdfs?->id,
            'A full breakdown of tuition and fees for every programme, by faculty and level of study.',
            'fee-structure-2026', ['PNK Global Campus', 'Fee Structure 2026', '', 'This is a demo placeholder document generated for content evaluation purposes.']);

        $csCourse = Course::where('course_code', 'CS-BSC-001')->first();
        $mbaCourse = Course::where('course_code', 'BUS-MBA-001')->first();
        $brochureDownload = Download::where('title', 'Faculty of Computing Brochure')->first();

        if ($brochureDownload && $csCourse) {
            $this->attachDownloadTo($brochureDownload, $csCourse);
        }

        if ($feeStructure && $mbaCourse) {
            $this->attachDownloadTo($feeStructure, $mbaCourse);
        }

        if ($feeStructure && $csCourse) {
            $this->attachDownloadTo($feeStructure, $csCourse);
        }
    }

    private function enrichDownload(string $title, string $slug, array $pdfLines): void
    {
        $download = Download::where('title', $title)->first();

        if (! $download) {
            return;
        }

        $this->attachDocument($download, 'file', $this->images->pdf($slug, $pdfLines[1], $pdfLines), "{$title}.pdf", $title);
    }

    /** Demonstrates the download-gating feature (requires_inquiry) on one pre-existing, previously-unconfigured record. */
    private function enrichGatedDownload(string $title, string $slug, array $pdfLines): void
    {
        $download = Download::where('title', $title)->first();

        if (! $download || $download->getMedia('file')->isNotEmpty()) {
            return;
        }

        if (! $download->requires_inquiry) {
            $download->update(['requires_inquiry' => true]);
        }

        $this->attachDocument($download, 'file', $this->images->pdf($slug, $pdfLines[1], $pdfLines), "{$title}.pdf", $title);
    }

    private function createDownload(string $title, ?int $categoryId, string $description, string $slug, array $pdfLines): ?Download
    {
        $download = Download::firstOrCreate(
            ['title' => $title],
            ['category_id' => $categoryId, 'description' => $description, 'order' => Download::count(), 'is_active' => true]
        );

        $this->attachDocument($download, 'file', $this->images->pdf($slug, $pdfLines[1], $pdfLines), "{$title}.pdf", $title);

        return $download;
    }

    private function attachDownloadTo(Download $download, Course $course): void
    {
        $exists = DownloadAttachment::where('download_id', $download->id)
            ->where('attachable_id', $course->id)
            ->where('attachable_type', $course->getMorphClass())
            ->exists();

        if ($exists) {
            return;
        }

        $attachment = new DownloadAttachment(['download_id' => $download->id]);
        $attachment->attachable()->associate($course);
        $attachment->save();
    }

    private function paragraph(string $topic): string
    {
        return "Continue reading for the full story on {$topic}, including background context and what it means for our students and the wider campus community.";
    }
}
