<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the Static/Builder Pages named in the SRS (Section 6.5): About,
 * Vision, Mission, Chairman's Message, Admissions, How to Apply,
 * Scholarships, International Students, Student Life, Career, Contact,
 * Privacy Policy, Terms & Conditions, Refund Policy — each published
 * with real Page Builder blocks, not empty stubs, so the admin Page
 * Builder has real content to demonstrate on first login ("How to
 * Apply", "Scholarships", and "Contact" were added as part of the
 * Public Website milestone — named in the SRS/nav structure from the
 * start, but never actually created). Blocks that need a Media Library
 * asset (image, gallery, video, partner logos) are left out of seed
 * data on purpose: no media exists on a fresh install, and fabricating
 * a fake media_id would violate the same "everything from DB, nothing
 * hardcoded" principle this milestone is built around. Idempotent — a
 * page's blocks are only created the first time that page itself is
 * created.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->page('About', 'about', [
            $this->hero('About PN Knowledge Campus', 'Building futures through knowledge, character, and community.'),
            $this->richText('<p>PN Knowledge Campus has served students and the wider community for years, combining rigorous academics with a supportive campus life.</p>'),
        ]);

        $this->page('Vision', 'vision', [
            $this->hero('Our Vision'),
            $this->text('To be a globally recognized center of academic excellence, innovation, and character development.'),
        ]);

        $this->page('Mission', 'mission', [
            $this->hero('Our Mission'),
            $this->text('To provide accessible, high-quality education that empowers students to lead and serve with integrity.'),
        ]);

        $this->page("Chairman's Message", 'chairmans-message', [
            $this->hero("Chairman's Message"),
            $this->richText('<p>Welcome to PN Knowledge Campus. It is our privilege to guide every student toward a future built on knowledge and character.</p>'),
            $this->testimonials([
                ['quote' => 'Education is the foundation on which we build a better future for every student who walks through our doors.', 'name' => 'The Chairman', 'role' => 'Chairman, PN Knowledge Campus'],
            ]),
        ]);

        $this->page('Admissions', 'admissions', [
            $this->hero('Admissions', 'Start your journey with us today.'),
            $this->cta('Ready to apply?', 'Our admissions team is here to help at every step.', 'Contact Admissions', '/contact'),
            $this->faq([
                ['question' => 'When does the admissions cycle open?', 'answer' => 'Admissions open twice a year; check the Admissions office for exact dates.'],
                ['question' => 'What documents are required?', 'answer' => 'Academic transcripts, identification, and a completed application form.'],
            ]),
        ]);

        $this->page('How to Apply', 'how-to-apply', [
            $this->hero('How to Apply', 'Four simple steps to start your application.'),
            $this->richText('<p>Applying to PN Knowledge Campus is straightforward — choose your course, submit your documents, and our Admissions team will guide you through the rest.</p>'),
            $this->cta('Have a question?', 'Reach out to our Admissions team at any point in the process.', 'Contact Admissions', '/contact'),
        ]);

        $this->page('Scholarships', 'scholarships', [
            $this->hero('Scholarships', 'Financial support for students who qualify.'),
            $this->text('PN Knowledge Campus offers a range of merit- and need-based scholarships across our Faculties. Speak with Admissions to find out which ones you may be eligible for.'),
            $this->faq([
                ['question' => 'When should I apply for a scholarship?', 'answer' => 'Scholarship applications open alongside each admissions cycle — apply at the same time as your course application.'],
            ]),
        ]);

        $this->page('International Students', 'international-students', [
            $this->hero('International Students'),
            $this->text('PN Knowledge Campus welcomes students from around the world, offering dedicated support for visas, housing, and orientation.'),
            $this->faq([
                ['question' => 'Do you offer visa support?', 'answer' => 'Yes, our International Office assists with visa applications and documentation.'],
            ]),
        ]);

        $this->page('Student Life', 'student-life', [
            $this->hero('Student Life'),
            $this->statistics([
                ['label' => 'Student Clubs', 'value' => '40+'],
                ['label' => 'Annual Events', 'value' => '25+'],
                ['label' => 'Sports Teams', 'value' => '12'],
            ]),
            $this->text('From clubs and societies to sports and cultural events, campus life here is as enriching as the classroom.'),
        ]);

        $this->page('Career', 'career', [
            $this->hero('Careers at PN Knowledge Campus'),
            $this->cta('Join our team', 'We are always looking for passionate educators and staff.', 'View Openings', '/contact'),
        ]);

        // Contact's own hero copy — the actual form and Settings-driven
        // contact details/branch list/map render as a bespoke React
        // component on the public site, not a Page Builder block type,
        // since "contact form" isn't one of the SRS's documented block
        // types. This page record just gives the Contact route a real
        // hero/intro to render above that component.
        $this->page('Contact', 'contact', [
            $this->hero('Contact Us', "We'd love to hear from you."),
        ]);

        $this->page('Privacy Policy', 'privacy-policy', [
            $this->hero('Privacy Policy'),
            $this->richText('<p>This Privacy Policy describes how PN Knowledge Campus collects, uses, and protects your personal information.</p>'),
        ]);

        $this->page('Terms & Conditions', 'terms', [
            $this->hero('Terms & Conditions'),
            $this->richText('<p>By using this website and our services, you agree to the following terms and conditions.</p>'),
        ]);

        $this->page('Refund Policy', 'refund-policy', [
            $this->hero('Refund Policy'),
            $this->richText('<p>This Refund Policy outlines the circumstances under which fees paid to PN Knowledge Campus may be refunded.</p>'),
        ]);
    }

    private function page(string $title, string $slug, array $blocks): void
    {
        $page = Page::firstOrCreate(
            ['slug' => $slug],
            ['title' => $title, 'status' => 'published', 'published_at' => Carbon::now()]
        );

        if (! $page->wasRecentlyCreated) {
            return;
        }

        foreach ($blocks as $order => [$blockType, $data]) {
            $page->blocks()->create(['block_type' => $blockType, 'data' => $data, 'order' => $order]);
        }
    }

    private function hero(string $heading, ?string $subheading = null): array
    {
        return ['hero', ['heading' => $heading, 'subheading' => $subheading, 'alignment' => 'center']];
    }

    private function text(string $body): array
    {
        return ['text', ['body' => $body]];
    }

    private function richText(string $body): array
    {
        return ['rich_text', ['body' => $body]];
    }

    private function cta(string $heading, string $body, string $buttonLabel, string $buttonUrl): array
    {
        return ['cta', ['heading' => $heading, 'body' => $body, 'button_label' => $buttonLabel, 'button_url' => $buttonUrl, 'style' => 'primary']];
    }

    private function faq(array $items): array
    {
        return ['faq', ['items' => $items]];
    }

    private function statistics(array $items): array
    {
        return ['statistics', ['items' => $items]];
    }

    private function testimonials(array $items): array
    {
        return ['testimonials', ['items' => $items]];
    }
}
