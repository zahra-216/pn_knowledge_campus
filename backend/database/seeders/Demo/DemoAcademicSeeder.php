<?php

namespace Database\Seeders\Demo;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLevel;
use App\Models\CourseMode;
use App\Models\Department;
use App\Models\Faculty;
use Database\Seeders\Demo\Concerns\SeedsMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Content Population pass (demo data) — enriches the 3 faculties/6
 * departments/3 courses FacultySeeder/DepartmentSeeder/CourseSeeder
 * already ship with (adding media, dean photos, galleries, entry
 * requirements/learning outcomes/career opportunities, SEO) and adds a
 * 4th faculty (Health Sciences) plus enough departments/courses to
 * reach the "realistic demo catalog" bar: 4 faculties, 12 departments,
 * 15 published courses spanning every CourseLevel/CourseMode.
 *
 * Every `firstOrCreate`/`getMedia()->isEmpty()` guard means this is
 * safe to run against a database an admin has already been using —
 * nothing here overwrites a real edit.
 */
class DemoAcademicSeeder extends Seeder
{
    use SeedsMedia;

    private DemoImageLibrary $images;

    public function run(): void
    {
        $this->images = new DemoImageLibrary;

        $this->enrichExistingFaculties();
        $this->createHealthSciencesFaculty();
        $this->addDepartments();
        $this->extendCourseCategories();
        $this->enrichExistingCourses();
        $this->createNewCourses();
    }

    // ---------------------------------------------------------------
    // Faculties
    // ---------------------------------------------------------------

    private function enrichExistingFaculties(): void
    {
        $this->enrichFaculty('faculty-of-business', 'graduates_walking', 'professor_portrait_1', ['business_meeting_table', 'marketing_brainstorm'],
            'Building the next generation of business leaders and entrepreneurs, the Faculty of Business pairs a rigorous core curriculum in management, finance, and marketing with real consultancy projects for local employers. Students graduate with a portfolio of real client work, not just case studies.');

        $this->enrichFaculty('faculty-of-engineering', 'campus_building', 'professor_portrait_2', ['civil_engineering_site', 'electrical_circuit'],
            'The Faculty of Engineering delivers rigorous, lab-heavy programmes in civil, mechanical, and electrical engineering. Every student completes at least one industry-sponsored capstone project before graduating, backed by partnerships with regional infrastructure and manufacturing employers.');

        $this->enrichFaculty('faculty-of-computing', 'students_studying_table', 'professor_portrait_3', ['laptop_coding', 'computer_lab_students'],
            'The Faculty of Computing combines strong theoretical foundations — algorithms, systems, data structures — with practical software development, data science, and cybersecurity training in dedicated labs refreshed every year to keep pace with industry tooling.');
    }

    private function enrichFaculty(string $slug, string $bannerKey, string $deanPhotoKey, array $galleryKeys, string $description): void
    {
        $faculty = Faculty::where('slug', $slug)->first();

        if (! $faculty) {
            return;
        }

        $this->attachImage($faculty, 'banner', $this->images, $bannerKey, "{$faculty->name} banner image");
        $this->attachImage($faculty, 'dean_photo', $this->images, $deanPhotoKey, "Portrait of {$faculty->dean_name}");
        $this->attachGalleryImages($faculty, 'gallery', $this->images, $galleryKeys);

        if (! $faculty->seoMeta()->exists()) {
            $faculty->seoMeta()->create([
                'seo_title' => "{$faculty->name} | PNK Global Campus",
                'meta_description' => Str::limit($description, 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }

    private function createHealthSciencesFaculty(): void
    {
        $faculty = Faculty::firstOrCreate(
            ['slug' => 'faculty-of-health-sciences'],
            [
                'name' => 'Faculty of Health Sciences',
                'short_description' => 'Training compassionate, clinically skilled healthcare professionals for a changing world.',
                'description' => 'The Faculty of Health Sciences prepares nurses, public health practitioners, and medical laboratory scientists through a blend of rigorous coursework and supervised clinical placements at partner hospitals and community health centres. Small cohort sizes mean every student gets direct clinical mentorship from qualified practicing professionals.',
                'dean_name' => 'Dr. Fatima Al-Rashid',
                'dean_title' => 'Dean, Faculty of Health Sciences',
                'dean_message' => 'Healthcare education is a responsibility as much as a discipline — our graduates leave here ready to care for real patients from their very first shift.',
                'order' => 3,
                'status' => 'published',
            ]
        );

        $this->attachImage($faculty, 'banner', $this->images, 'students_walking', "{$faculty->name} banner image");
        $this->attachImage($faculty, 'dean_photo', $this->images, 'doctor_professional', 'Portrait of Dr. Fatima Al-Rashid');
        $this->attachGalleryImages($faculty, 'gallery', $this->images, ['medical_team', 'medical_lab_research']);

        if (! $faculty->seoMeta()->exists()) {
            $faculty->seoMeta()->create([
                'seo_title' => 'Faculty of Health Sciences | PNK Global Campus',
                'meta_description' => 'Nursing, Public Health, and Medical Laboratory Science programmes at PNK Global Campus, backed by clinical placements at partner hospitals.',
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // Departments
    // ---------------------------------------------------------------

    private function addDepartments(): void
    {
        $this->department('faculty-of-business', 'Department of Business Administration', 'department-of-business-administration', 2, 'business_discussion');
        $this->department('faculty-of-engineering', 'Department of Mechanical Engineering', 'department-of-mechanical-engineering', 2, 'engineering_workshop');
        $this->department('faculty-of-computing', 'Department of Data Science & Artificial Intelligence', 'department-of-data-science-artificial-intelligence', 2, 'robotics_tech');

        $this->department('faculty-of-health-sciences', 'Department of Nursing', 'department-of-nursing', 0, 'nursing_student');
        $this->department('faculty-of-health-sciences', 'Department of Public Health', 'department-of-public-health', 1, 'medical_team');
        $this->department('faculty-of-health-sciences', 'Department of Medical Laboratory Science', 'department-of-medical-laboratory-science', 2, 'medical_lab_research');

        // Backfill banners on the 4 pre-existing departments too.
        $this->departmentBanner('department-of-accounting-finance', 'finance_charts');
        $this->departmentBanner('department-of-marketing', 'marketing_brainstorm');
        $this->departmentBanner('department-of-civil-engineering', 'civil_engineering_site');
        $this->departmentBanner('department-of-electrical-engineering', 'electrical_circuit');
        $this->departmentBanner('department-of-computer-science', 'computer_lab_students');
        $this->departmentBanner('department-of-software-engineering', 'laptop_coding');
    }

    private function department(string $facultySlug, string $name, string $slug, int $order, string $bannerKey): void
    {
        $faculty = Faculty::where('slug', $facultySlug)->first();

        if (! $faculty) {
            return;
        }

        $department = Department::firstOrCreate(
            ['slug' => $slug],
            ['faculty_id' => $faculty->id, 'name' => $name, 'order' => $order, 'status' => 'published']
        );

        $this->attachImage($department, 'banner', $this->images, $bannerKey, "{$name} banner image");
    }

    private function departmentBanner(string $slug, string $bannerKey): void
    {
        $department = Department::where('slug', $slug)->first();

        if ($department) {
            $this->attachImage($department, 'banner', $this->images, $bannerKey, "{$department->name} banner image");
        }
    }

    // ---------------------------------------------------------------
    // Course lookups
    // ---------------------------------------------------------------

    private function extendCourseCategories(): void
    {
        $healthMedicine = CourseCategory::firstOrCreate(['slug' => 'health-medicine'], ['name' => 'Health & Medicine', 'order' => 3]);
        CourseCategory::firstOrCreate(['slug' => 'nursing'], ['name' => 'Nursing', 'order' => 0, 'parent_id' => $healthMedicine->id]);
        CourseCategory::firstOrCreate(['slug' => 'public-health'], ['name' => 'Public Health', 'order' => 1, 'parent_id' => $healthMedicine->id]);

        $computing = CourseCategory::where('slug', 'computing-it')->first();

        if ($computing) {
            CourseCategory::firstOrCreate(['slug' => 'cybersecurity'], ['name' => 'Cybersecurity', 'order' => 1, 'parent_id' => $computing->id]);
        }
    }

    // ---------------------------------------------------------------
    // Existing courses — enrichment only
    // ---------------------------------------------------------------

    private function enrichExistingCourses(): void
    {
        $this->enrichCourse('CS-BSC-001', [
            // Featured image intentionally NOT attached here — this course
            // already has an admin-uploaded featured_image (course id 1);
            // attachImage()'s "skip if collection non-empty" guard protects it
            // regardless, this note just explains why no key is passed below.
            'gallery' => ['computer_lab_students', 'laptop_coding'],
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics, or an equivalent recognised qualification. Applicants without a formal Mathematics pass may qualify via the Foundation Year pathway.',
            'learning_outcomes' => "Design, implement, and test software systems using modern languages and frameworks.\nApply core algorithms and data structures to solve real computational problems efficiently.\nDesign and query relational and non-relational databases.\nWork effectively within an agile software development team.",
            'career_opportunities' => 'Graduates go on to roles such as Software Engineer, Full-Stack Developer, Systems Analyst, and Junior DevOps Engineer, across both local technology employers and international remote-first companies.',
            'seo_title' => 'BSc (Hons) Computer Science | PNK Global Campus',
        ]);

        $this->enrichCourse('CS-BSC-002', [
            'featured_image' => 'tech_lab_people',
            'gallery' => ['robotics_tech', 'computer_lab_students'],
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics. No prior programming experience is required — the first year builds programming fundamentals from scratch.',
            'learning_outcomes' => "Apply statistical methods to real-world datasets.\nBuild and evaluate machine learning models for classification, regression, and clustering tasks.\nDesign data pipelines for collecting, cleaning, and storing large datasets.\nCommunicate data-driven findings clearly to non-technical stakeholders.",
            'career_opportunities' => 'Graduates pursue roles such as Data Analyst, Junior Data Scientist, Business Intelligence Analyst, and Machine Learning Engineer.',
            'seo_title' => 'BSc (Hons) Data Science | PNK Global Campus',
        ]);

        $this->enrichCourse('BUS-DIP-001', [
            'featured_image' => 'finance_charts',
            'gallery' => ['accounting_calculator', 'business_meeting_table'],
            'entry_requirements' => 'GCE O/Level passes including Mathematics and English, or two years of relevant work experience in an accounting or finance role.',
            'learning_outcomes' => "Prepare and interpret core financial statements.\nApply fundamental principles of taxation and payroll processing.\nUse spreadsheet-based tools for budgeting and financial forecasting.\nUnderstand the regulatory environment governing financial reporting.",
            'career_opportunities' => 'Graduates typically move into roles such as Accounts Assistant, Junior Bookkeeper, Payroll Administrator, or progress to a top-up BA (Hons) Business Administration degree.',
            'seo_title' => 'Diploma in Accounting & Finance | PNK Global Campus',
        ]);
    }

    private function enrichCourse(string $code, array $fields): void
    {
        $course = Course::where('course_code', $code)->first();

        if (! $course) {
            return;
        }

        if (isset($fields['featured_image'])) {
            $this->attachImage($course, 'featured_image', $this->images, $fields['featured_image'], "{$course->course_name} featured image");
        }

        $this->attachGalleryImages($course, 'gallery', $this->images, $fields['gallery'] ?? []);

        $course->update(array_filter([
            'entry_requirements' => $course->entry_requirements ?: ($fields['entry_requirements'] ?? null),
            'learning_outcomes' => $course->learning_outcomes ?: ($fields['learning_outcomes'] ?? null),
            'career_opportunities' => $course->career_opportunities ?: ($fields['career_opportunities'] ?? null),
        ]));

        if (! $course->seoMeta()->exists() && isset($fields['seo_title'])) {
            $course->seoMeta()->create([
                'seo_title' => $fields['seo_title'],
                'meta_description' => Str::limit($course->overview, 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // New courses
    // ---------------------------------------------------------------

    private function createNewCourses(): void
    {
        $this->course([
            'faculty' => 'faculty-of-computing', 'department' => 'department-of-software-engineering',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Software Development',
            'name' => 'BSc (Hons) Software Engineering', 'code' => 'CS-BSC-003', 'duration_value' => 3, 'price' => 4600.00,
            'is_featured' => true, 'featured_image' => 'computer_lab_students', 'gallery' => ['laptop_coding', 'tech_lab_people'],
            'overview' => 'A project-driven degree focused on building production-quality software as part of real engineering teams, not just individual assignments.',
            'description' => 'This programme teaches software engineering as a discipline, not just programming: version control, code review, testing, CI/CD pipelines, and agile delivery are woven through every year, culminating in a two-semester team capstone building a real product for an external client.',
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics.',
            'learning_outcomes' => "Apply software engineering best practices including testing, version control, and CI/CD.\nDesign scalable software architectures for web and mobile applications.\nWork within an agile team to deliver a production-quality capstone project.",
            'career_opportunities' => 'Software Engineer, Backend Developer, Mobile Developer, DevOps Engineer, Engineering Team Lead (with experience).',
            'curriculum' => [
                ['title' => 'Year 1: Foundations', 'children' => [['title' => 'Programming Fundamentals', 'duration' => '12 weeks'], ['title' => 'Object-Oriented Design', 'duration' => '12 weeks']]],
                ['title' => 'Year 2: Engineering Practice', 'children' => [['title' => 'Software Testing & QA', 'duration' => '12 weeks'], ['title' => 'DevOps & CI/CD', 'duration' => '10 weeks']]],
                ['title' => 'Year 3: Capstone', 'children' => [['title' => 'Team Capstone Project', 'duration' => '24 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Is this different from the Computer Science degree?', 'answer' => 'Yes — Software Engineering is more team- and delivery-focused, with a two-semester client capstone, while Computer Science has a broader theoretical base.'],
                ['question' => 'Is there an industry placement?', 'answer' => 'An optional one-semester industry placement is available between Year 2 and Year 3.'],
            ],
            'seo_title' => 'BSc (Hons) Software Engineering | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-computing', 'department' => 'department-of-data-science-artificial-intelligence',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Cybersecurity',
            'name' => 'BSc (Hons) Cybersecurity', 'code' => 'CS-BSC-004', 'duration_value' => 3, 'price' => 4900.00,
            'is_featured' => false, 'featured_image' => 'cybersecurity_lock', 'gallery' => ['tech_lab_people'],
            'overview' => 'A hands-on degree in network security, ethical hacking, and digital forensics, taught in a dedicated penetration-testing lab.',
            'description' => 'Students learn to think like both defenders and attackers: secure network design, incident response, and digital forensics sit alongside supervised ethical hacking exercises in an isolated lab environment built for this programme.',
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics; a pass in a Computing-related subject is preferred but not required.',
            'learning_outcomes' => "Identify and mitigate common network and application security vulnerabilities.\nConduct authorised penetration tests within a defined scope and ethical framework.\nRespond to and investigate a simulated security incident, including basic digital forensics.",
            'career_opportunities' => 'Security Analyst, SOC Analyst, Junior Penetration Tester, IT Risk & Compliance Officer.',
            'curriculum' => [
                ['title' => 'Year 1: Foundations', 'children' => [['title' => 'Networking Fundamentals', 'duration' => '12 weeks'], ['title' => 'Operating Systems Security', 'duration' => '12 weeks']]],
                ['title' => 'Year 2: Offensive & Defensive Security', 'children' => [['title' => 'Ethical Hacking', 'duration' => '12 weeks'], ['title' => 'Digital Forensics', 'duration' => '10 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Do I need to already know how to code?', 'answer' => 'Basic scripting is taught in Year 1 — prior programming experience helps but is not required.'],
            ],
            'seo_title' => 'BSc (Hons) Cybersecurity | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-computing', 'department' => 'department-of-data-science-artificial-intelligence',
            'level' => 'Postgraduate', 'mode' => 'Full-Time', 'category' => 'Computing & IT',
            'name' => 'MSc Data Science & Artificial Intelligence', 'code' => 'CS-MSC-001', 'duration_value' => 1, 'price' => 6200.00,
            'is_featured' => true, 'featured_image' => 'robotics_tech', 'gallery' => ['tech_lab_people', 'laptop_coding'],
            'overview' => 'An intensive one-year Master\'s for graduates looking to specialise in applied machine learning and AI systems.',
            'description' => 'Building on an undergraduate foundation in a numerate discipline, this Master\'s goes deep into deep learning, natural language processing, and MLOps, taught by faculty active in applied research, and includes an industry-partnered dissertation project.',
            'entry_requirements' => 'A Bachelor\'s degree (2:2 or above) in Computer Science, Mathematics, Engineering, or a related numerate discipline.',
            'learning_outcomes' => "Design and train deep learning models for vision and language tasks.\nDeploy machine learning models to production using MLOps practices.\nCritically evaluate the ethical implications of AI systems.\nComplete an original research dissertation with an industry partner.",
            'career_opportunities' => 'Machine Learning Engineer, AI Research Engineer, Data Scientist, MLOps Engineer.',
            'curriculum' => [
                ['title' => 'Semester 1: Core Theory', 'children' => [['title' => 'Deep Learning', 'duration' => '10 weeks'], ['title' => 'Natural Language Processing', 'duration' => '10 weeks']]],
                ['title' => 'Semester 2: Dissertation', 'children' => [['title' => 'Industry-Partnered Dissertation', 'duration' => '16 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Is a thesis required?', 'answer' => 'Yes — a supervised dissertation project, often with an industry partner, in the second semester.'],
            ],
            'seo_title' => 'MSc Data Science & Artificial Intelligence | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-engineering', 'department' => 'department-of-civil-engineering',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Engineering & Technology',
            'name' => 'BEng (Hons) Civil Engineering', 'code' => 'ENG-BEN-001', 'duration_value' => 4, 'price' => 5100.00,
            'is_featured' => true, 'featured_image' => 'civil_engineering_site', 'gallery' => ['engineering_workshop'],
            'overview' => 'A four-year accredited programme covering structural, geotechnical, and transportation engineering, with a full year of site-based practical training.',
            'description' => 'Students progress from foundational mechanics and materials science through to structural design studios and a year-long capstone project, with practical site visits to active construction projects with our industry partners throughout.',
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics and Physics.',
            'learning_outcomes' => "Apply structural analysis principles to design safe, efficient structures.\nEvaluate soil and foundation conditions for construction projects.\nApply relevant building codes and safety regulations.\nManage a construction project's schedule and budget at a basic level.",
            'career_opportunities' => 'Graduate Civil Engineer, Structural Engineering Assistant, Site Engineer, Construction Project Coordinator.',
            'curriculum' => [
                ['title' => 'Year 1-2: Foundations', 'children' => [['title' => 'Engineering Mechanics', 'duration' => '12 weeks'], ['title' => 'Materials Science', 'duration' => '12 weeks']]],
                ['title' => 'Year 3-4: Specialisation', 'children' => [['title' => 'Structural Design Studio', 'duration' => '16 weeks'], ['title' => 'Capstone Project', 'duration' => '24 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Is this programme professionally accredited?', 'answer' => 'Yes — it is accredited by the National Accreditation Board, a requirement for professional engineer registration.'],
            ],
            'seo_title' => 'BEng (Hons) Civil Engineering | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-engineering', 'department' => 'department-of-electrical-engineering',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Engineering & Technology',
            'name' => 'BEng (Hons) Electrical & Electronic Engineering', 'code' => 'ENG-BEN-002', 'duration_value' => 4, 'price' => 5100.00,
            'is_featured' => false, 'featured_image' => 'electrical_circuit', 'gallery' => ['engineering_workshop'],
            'overview' => 'Covers power systems, embedded electronics, and control systems, with hands-on lab work every semester.',
            'description' => 'From circuit theory to power systems and embedded microcontroller programming, this programme balances electrical and electronic specialisms so graduates can move into either discipline — or a hybrid mechatronics role.',
            'entry_requirements' => 'Three GCE A/Level passes including Mathematics and Physics.',
            'learning_outcomes' => "Design and analyse analog and digital circuits.\nProgram embedded microcontrollers for real-world control systems.\nApply power systems principles to electrical distribution design.",
            'career_opportunities' => 'Graduate Electrical Engineer, Embedded Systems Engineer, Power Systems Technician, Control Systems Engineer.',
            'curriculum' => [
                ['title' => 'Year 1-2: Foundations', 'children' => [['title' => 'Circuit Theory', 'duration' => '12 weeks'], ['title' => 'Digital Electronics', 'duration' => '12 weeks']]],
                ['title' => 'Year 3-4: Specialisation', 'children' => [['title' => 'Power Systems', 'duration' => '12 weeks'], ['title' => 'Embedded Systems Capstone', 'duration' => '20 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Can I specialise in embedded systems?', 'answer' => 'Yes — Year 4 offers an embedded/mechatronics elective track alongside the power systems track.'],
            ],
            'seo_title' => 'BEng (Hons) Electrical & Electronic Engineering | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-engineering', 'department' => 'department-of-mechanical-engineering',
            'level' => 'Diploma', 'mode' => 'Part-Time', 'category' => 'Engineering & Technology',
            'name' => 'Diploma in Mechanical Engineering', 'code' => 'ENG-DIP-001', 'duration_value' => 24, 'duration_unit' => 'month', 'price' => 2600.00, 'discount_price' => 2300.00,
            'is_featured' => false, 'featured_image' => 'engineering_workshop', 'gallery' => [],
            'overview' => 'A part-time diploma for working technicians looking to formalise their skills in mechanical design and manufacturing.',
            'description' => 'Delivered on evenings and weekends to accommodate working professionals, this diploma covers manufacturing processes, thermodynamics fundamentals, and CAD-based mechanical design.',
            'entry_requirements' => 'GCE O/Level passes including Mathematics, or two years of relevant technical work experience.',
            'learning_outcomes' => "Produce mechanical component designs using CAD software.\nApply thermodynamics fundamentals to basic mechanical systems.\nUnderstand core manufacturing processes and material selection.",
            'career_opportunities' => 'Mechanical Design Technician, Manufacturing Technician, Maintenance Engineer, progression to a top-up BEng degree.',
            'curriculum' => [
                ['title' => 'Core Modules', 'children' => [['title' => 'CAD & Mechanical Design', 'duration' => '16 weeks'], ['title' => 'Manufacturing Processes', 'duration' => '16 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Can I study this alongside a full-time job?', 'answer' => 'Yes — all classes are scheduled evenings and weekends specifically for working students.'],
            ],
            'seo_title' => 'Diploma in Mechanical Engineering | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-business', 'department' => 'department-of-business-administration',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Business & Management',
            'name' => 'BA (Hons) Business Administration', 'code' => 'BUS-BA-001', 'duration_value' => 3, 'price' => 4300.00,
            'is_featured' => true, 'featured_image' => 'business_meeting_team', 'gallery' => ['team_handshake', 'business_discussion'],
            'overview' => 'A broad-based business degree covering strategy, operations, HR, and finance — ideal preparation for management roles or further specialisation.',
            'description' => 'Rather than specialising immediately, this degree gives students a strong general management foundation across all core business functions, with a Year 3 elective allowing focus in finance, marketing, or operations.',
            'entry_requirements' => 'Three GCE A/Level passes in any subject.',
            'learning_outcomes' => "Analyse business strategy using established frameworks.\nApply core principles of operations, HR, and financial management.\nDevelop and present a business plan for a new venture.",
            'career_opportunities' => 'Management Trainee, Business Analyst, Operations Coordinator, Junior Consultant.',
            'curriculum' => [
                ['title' => 'Year 1: Core Business', 'children' => [['title' => 'Principles of Management', 'duration' => '12 weeks'], ['title' => 'Financial Accounting', 'duration' => '12 weeks']]],
                ['title' => 'Year 3: Electives', 'children' => [['title' => 'Elective: Finance, Marketing, or Operations', 'duration' => '12 weeks']]],
            ],
            'faqs' => [
                ['question' => 'When do I choose my specialisation?', 'answer' => 'Electives are chosen at the start of Year 3, after a broad Years 1-2 foundation.'],
            ],
            'seo_title' => 'BA (Hons) Business Administration | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-business', 'department' => 'department-of-marketing',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Marketing & HR',
            'name' => 'BSc (Hons) Marketing Management', 'code' => 'BUS-BSC-001', 'duration_value' => 3, 'price' => 4300.00,
            'is_featured' => false, 'featured_image' => 'marketing_brainstorm', 'gallery' => ['business_meeting_table'],
            'overview' => 'Covers digital marketing, brand strategy, and consumer psychology, with a live client campaign every year.',
            'description' => 'Students run a real marketing campaign for a local business or nonprofit client every year, building a portfolio of live work in digital advertising, social media strategy, and brand development well before graduation.',
            'entry_requirements' => 'Three GCE A/Level passes in any subject.',
            'learning_outcomes' => "Develop and execute a digital marketing campaign across multiple channels.\nApply consumer psychology principles to brand positioning.\nAnalyse marketing campaign performance using real analytics data.",
            'career_opportunities' => 'Marketing Executive, Digital Marketing Specialist, Brand Assistant, Social Media Manager.',
            'curriculum' => [
                ['title' => 'Year 1-2: Foundations', 'children' => [['title' => 'Principles of Marketing', 'duration' => '12 weeks'], ['title' => 'Consumer Behaviour', 'duration' => '12 weeks']]],
                ['title' => 'Year 3: Live Client Campaign', 'children' => [['title' => 'Applied Campaign Project', 'duration' => '20 weeks']]],
            ],
            'faqs' => [
                ['question' => 'What is the "live client campaign"?', 'answer' => 'Each year, student teams plan and run a real marketing campaign for a local business or nonprofit, under faculty supervision.'],
            ],
            'seo_title' => 'BSc (Hons) Marketing Management | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-business', 'department' => 'department-of-business-administration',
            'level' => 'Postgraduate', 'mode' => 'Blended', 'category' => 'Business & Management',
            'name' => 'Master of Business Administration (MBA)', 'code' => 'BUS-MBA-001', 'duration_value' => 18, 'duration_unit' => 'month', 'price' => 7800.00,
            'is_featured' => true, 'featured_image' => 'business_discussion', 'gallery' => ['job_interview', 'business_meeting_team'],
            'overview' => 'A blended-format MBA for working professionals, combining weekend residencies with online coursework.',
            'description' => 'Designed for professionals with at least two years of work experience, this MBA blends monthly weekend residencies with asynchronous online modules, covering strategy, leadership, finance, and a consultancy-style capstone project with a real organisation.',
            'entry_requirements' => 'A Bachelor\'s degree in any discipline plus a minimum of two years\' full-time work experience.',
            'learning_outcomes' => "Develop and defend corporate strategy at an executive level.\nLead cross-functional teams through organisational change.\nApply financial analysis to major investment decisions.\nDeliver a consultancy-style capstone project for a real organisation.",
            'career_opportunities' => 'Senior Manager, Business Development Manager, Management Consultant, Director-track roles.',
            'curriculum' => [
                ['title' => 'Core Modules', 'children' => [['title' => 'Corporate Strategy', 'duration' => '10 weeks'], ['title' => 'Financial Management', 'duration' => '10 weeks']]],
                ['title' => 'Capstone', 'children' => [['title' => 'Consultancy Capstone Project', 'duration' => '16 weeks']]],
            ],
            'faqs' => [
                ['question' => 'How often are the in-person residencies?', 'answer' => 'One weekend per month; all other coursework is completed online at your own pace within each module\'s deadline.'],
                ['question' => 'Is work experience mandatory?', 'answer' => 'Yes, a minimum of two years\' full-time work experience is required to apply.'],
            ],
            'seo_title' => 'Master of Business Administration (MBA) | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-health-sciences', 'department' => 'department-of-nursing',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Nursing',
            'name' => 'BSc (Hons) Nursing', 'code' => 'HS-BSC-001', 'duration_value' => 4, 'price' => 5400.00,
            'is_featured' => true, 'featured_image' => 'nursing_student', 'gallery' => ['medical_team', 'medical_students'],
            'overview' => 'A clinically-intensive nursing degree combining classroom instruction with supervised placements at partner hospitals from Year 1.',
            'description' => 'Students begin supervised clinical placements in their very first year, building toward independent practice by Year 4. The curriculum covers adult, mental health, and community nursing, taught by faculty who remain in active clinical practice.',
            'entry_requirements' => 'Three GCE A/Level passes including Biology and Chemistry. All offers are subject to a satisfactory background check, per healthcare placement requirements.',
            'learning_outcomes' => "Deliver safe, evidence-based nursing care across the lifespan.\nApply clinical reasoning to assess and respond to changing patient conditions.\nWork effectively within a multidisciplinary healthcare team.\nUphold professional and ethical standards of nursing practice.",
            'career_opportunities' => 'Registered Nurse, Community Health Nurse, Clinical Care Coordinator, progression to specialist postgraduate nursing programmes.',
            'curriculum' => [
                ['title' => 'Year 1-2: Foundations & Placement', 'children' => [['title' => 'Anatomy & Physiology', 'duration' => '12 weeks'], ['title' => 'Supervised Clinical Placement I', 'duration' => '8 weeks']]],
                ['title' => 'Year 3-4: Specialisation', 'children' => [['title' => 'Mental Health Nursing', 'duration' => '10 weeks'], ['title' => 'Supervised Clinical Placement II', 'duration' => '16 weeks']]],
            ],
            'faqs' => [
                ['question' => 'When do clinical placements start?', 'answer' => 'Supervised placements begin in the second semester of Year 1, at one of our partner hospitals.'],
                ['question' => 'Is this programme recognised by the Nursing & Midwifery Council?', 'answer' => 'Yes — graduates are eligible to register as a Registered Nurse on successful completion.'],
            ],
            'seo_title' => 'BSc (Hons) Nursing | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-health-sciences', 'department' => 'department-of-public-health',
            'level' => 'Undergraduate', 'mode' => 'Full-Time', 'category' => 'Public Health',
            'name' => 'BSc (Hons) Public Health', 'code' => 'HS-BSC-002', 'duration_value' => 3, 'price' => 4700.00,
            'is_featured' => false, 'featured_image' => 'medical_team', 'gallery' => ['medical_lab_research'],
            'overview' => 'Covers epidemiology, health policy, and community health programme design, with a community-based practicum in the final year.',
            'description' => 'Students learn to design, run, and evaluate public health interventions, from epidemiological data analysis to community health education campaigns, culminating in a practicum placement with a regional health authority or NGO.',
            'entry_requirements' => 'Three GCE A/Level passes including at least one science subject.',
            'learning_outcomes' => "Analyse epidemiological data to identify public health trends.\nDesign a community health intervention programme.\nEvaluate health policy for population-level impact.",
            'career_opportunities' => 'Public Health Officer, Community Health Programme Coordinator, Health Policy Research Assistant.',
            'curriculum' => [
                ['title' => 'Year 1-2: Foundations', 'children' => [['title' => 'Introduction to Epidemiology', 'duration' => '12 weeks'], ['title' => 'Health Policy & Systems', 'duration' => '12 weeks']]],
                ['title' => 'Year 3: Practicum', 'children' => [['title' => 'Community Health Practicum', 'duration' => '14 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Is this a clinical programme?', 'answer' => 'No — Public Health focuses on population-level health rather than direct clinical patient care, unlike Nursing or Medical Laboratory Science.'],
            ],
            'seo_title' => 'BSc (Hons) Public Health | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-health-sciences', 'department' => 'department-of-medical-laboratory-science',
            'level' => 'Diploma', 'mode' => 'Full-Time', 'category' => 'Health & Medicine',
            'name' => 'Diploma in Medical Laboratory Science', 'code' => 'HS-DIP-001', 'duration_value' => 2, 'duration_unit' => 'year', 'price' => 3400.00,
            'is_featured' => false, 'featured_image' => 'medical_lab_research', 'gallery' => ['chemistry_lab'],
            'overview' => 'A practical, lab-based diploma preparing technicians for diagnostic laboratory work in hospitals and clinics.',
            'description' => 'Covers haematology, microbiology, and clinical chemistry techniques in fully equipped teaching laboratories, with a supervised placement in a partner hospital\'s diagnostic laboratory in the final semester.',
            'entry_requirements' => 'GCE A/Level passes including Biology and Chemistry, or equivalent.',
            'learning_outcomes' => "Perform standard haematology and clinical chemistry laboratory tests accurately.\nFollow correct specimen handling and laboratory safety protocols.\nOperate and maintain common diagnostic laboratory equipment.",
            'career_opportunities' => 'Medical Laboratory Technician, Diagnostic Laboratory Assistant, progression to a top-up BSc in Medical Laboratory Science.',
            'curriculum' => [
                ['title' => 'Core Modules', 'children' => [['title' => 'Clinical Chemistry', 'duration' => '14 weeks'], ['title' => 'Haematology & Microbiology', 'duration' => '14 weeks']]],
                ['title' => 'Final Semester', 'children' => [['title' => 'Supervised Laboratory Placement', 'duration' => '12 weeks']]],
            ],
            'faqs' => [
                ['question' => 'Where does the placement take place?', 'answer' => 'At the diagnostic laboratory of one of our partner hospitals, arranged by the Faculty in your final semester.'],
            ],
            'seo_title' => 'Diploma in Medical Laboratory Science | PNK Global Campus',
        ]);

        // Certificate-level — the header's Courses mega menu links to a
        // "Certificate Level" filter (level=certificate); without at least
        // one real course at that level, that link led to an empty result.
        $this->course([
            'faculty' => 'faculty-of-business', 'department' => 'department-of-marketing',
            'level' => 'Certificate', 'mode' => 'Part-Time', 'category' => 'Marketing & HR',
            'name' => 'Certificate in Digital Marketing', 'code' => 'BUS-CERT-001', 'duration_value' => 3, 'duration_unit' => 'month', 'price' => 650.00,
            'is_featured' => false, 'featured_image' => 'marketing_brainstorm', 'gallery' => ['business_meeting_table'],
            'overview' => 'A fast, practical introduction to search, social, and content marketing for working professionals looking to build in-demand digital skills.',
            'description' => 'Delivered over evening and weekend sessions so working professionals can attend alongside a job, this certificate covers SEO fundamentals, paid social advertising, content strategy, and campaign analytics, with a real campaign brief as the final assessment.',
            'entry_requirements' => 'Open to any applicant with GCE O/Level passes or equivalent work experience; no prior marketing background required.',
            'learning_outcomes' => "Plan and execute a basic search engine optimisation strategy.\nBuild and analyse a paid social media advertising campaign.\nDevelop a content calendar aligned to a brand's marketing goals.",
            'career_opportunities' => 'Digital Marketing Assistant, Social Media Coordinator, Marketing Executive (entry-level), or a foundation before progressing to the BA (Hons) Business Administration degree.',
            'faqs' => [
                ['question' => 'Can I study this alongside a full-time job?', 'answer' => 'Yes — classes run in the evenings and on weekends specifically for working professionals.'],
            ],
            'seo_title' => 'Certificate in Digital Marketing | PNK Global Campus',
        ]);

        $this->course([
            'faculty' => 'faculty-of-computing', 'department' => 'department-of-computer-science',
            'level' => 'Certificate', 'mode' => 'Online', 'category' => 'Cybersecurity',
            'name' => 'Certificate in Cybersecurity Awareness', 'code' => 'CS-CERT-001', 'duration_value' => 6, 'duration_unit' => 'week', 'price' => 450.00,
            'is_featured' => false, 'featured_image' => 'cybersecurity_lock', 'gallery' => ['laptop_coding'],
            'overview' => 'A short, fully online course covering the everyday security practices every employee and small business owner should know.',
            'description' => 'Taught entirely online across six weeks, this course covers phishing and social engineering, password and account hygiene, safe device and network use, and basic incident reporting — aimed at non-technical staff as much as IT-adjacent roles.',
            'entry_requirements' => 'No prior technical background required.',
            'learning_outcomes' => "Recognise common phishing and social engineering attempts.\nApply strong password and multi-factor authentication practices.\nFollow a basic incident-reporting process when something looks wrong.",
            'career_opportunities' => 'A foundation for roles with security-adjacent responsibilities, or a first step toward the BSc (Hons) Cybersecurity degree.',
            'faqs' => [
                ['question' => 'Is this a technical, hands-on security course?', 'answer' => 'No — it is an awareness-level course for a general audience. For hands-on technical training, see the BSc (Hons) Cybersecurity degree.'],
            ],
            'seo_title' => 'Certificate in Cybersecurity Awareness | PNK Global Campus',
        ]);
    }

    private function course(array $c): void
    {
        $faculty = Faculty::where('slug', $c['faculty'])->first();
        $department = Department::where('slug', $c['department'])->first();
        $level = CourseLevel::where('name', $c['level'])->first();
        $mode = CourseMode::where('name', $c['mode'])->first();
        $category = CourseCategory::where('name', $c['category'])->first();

        if (! $faculty || ! $department || ! $level || ! $mode) {
            return;
        }

        $course = Course::firstOrCreate(
            ['course_code' => $c['code']],
            [
                'faculty_id' => $faculty->id,
                'department_id' => $department->id,
                'level_id' => $level->id,
                'mode_id' => $mode->id,
                'category_id' => $category?->id,
                'course_name' => $c['name'],
                'slug' => Str::slug($c['name']),
                'duration_value' => $c['duration_value'],
                'duration_unit' => $c['duration_unit'] ?? 'year',
                'price' => $c['price'],
                'discount_price' => $c['discount_price'] ?? null,
                'overview' => $c['overview'],
                'description' => $c['description'],
                'entry_requirements' => $c['entry_requirements'] ?? null,
                'learning_outcomes' => $c['learning_outcomes'] ?? null,
                'career_opportunities' => $c['career_opportunities'] ?? null,
                'status' => 'published',
                'published_at' => now(),
                'is_featured' => $c['is_featured'],
            ]
        );

        // Media attachment is safe to run unconditionally (attachImage/
        // attachGalleryImages already guard on "is this collection
        // empty"), which matters when a media reset (e.g. swapping the
        // whole demo photo set) has to re-run against courses that
        // already exist from a previous seeding pass. Curriculum/FAQs
        // below have no such per-row guard, so those stay gated on
        // wasRecentlyCreated to avoid duplicating them on every re-run.
        if (isset($c['featured_image'])) {
            $this->attachImage($course, 'featured_image', $this->images, $c['featured_image'], "{$course->course_name} featured image");
        }

        $this->attachGalleryImages($course, 'gallery', $this->images, $c['gallery'] ?? []);

        if (! $course->wasRecentlyCreated) {
            return;
        }

        foreach ($c['curriculum'] ?? [] as $index => $module) {
            $created = $course->curriculumItems()->create(['title' => $module['title'], 'order' => $index]);

            foreach ($module['children'] ?? [] as $childIndex => $child) {
                $course->curriculumItems()->create([
                    'parent_id' => $created->id,
                    'title' => $child['title'],
                    'duration' => $child['duration'] ?? null,
                    'order' => $childIndex,
                ]);
            }
        }

        foreach ($c['faqs'] ?? [] as $index => $faq) {
            $course->faqs()->create(['question' => $faq['question'], 'answer' => $faq['answer'], 'order' => $index]);
        }

        if (isset($c['seo_title'])) {
            $course->seoMeta()->create([
                'seo_title' => $c['seo_title'],
                'meta_description' => Str::limit($c['overview'], 155),
                'robots_index' => true,
                'robots_follow' => true,
            ]);
        }
    }
}
