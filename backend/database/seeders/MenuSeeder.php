<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the 'header' and 'footer' menus (Database Design, Section 4.5)
 * with a starting structure taken from the SRS's default Navigation
 * Structure (Section 9) — every item is a custom_url link, since no
 * internal content model (Page, Course, ...) existed yet to link to
 * when this seeder first shipped (see config/menus.php). Idempotent
 * (firstOrCreate by menu name / label).
 *
 * URLs are flat, single-segment paths (`/vision`, `/faculties`,
 * `/privacy-policy`, ...) matching the public site's actual route
 * table and each Page Builder page's own (flat) slug — not the nested
 * `/about/vision`, `/academics/courses`, `/legal/privacy-policy` guesses
 * this seeder originally shipped with, before the Public Website
 * milestone settled on the real routing scheme.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $header = Menu::firstOrCreate(['name' => 'header']);
        $footer = Menu::firstOrCreate(['name' => 'footer']);

        $this->seedHeader($header);
        $this->seedFooter($footer);
    }

    private function seedHeader(Menu $menu): void
    {
        $this->item($menu, 'Home', '/', order: 0);
        $about = $this->item($menu, 'About', '/about', order: 1);
        $this->item($menu, 'Vision', '/vision', order: 0, parent: $about);
        $this->item($menu, 'Mission', '/mission', order: 1, parent: $about);
        $this->item($menu, "Chairman's Message", '/chairmans-message', order: 2, parent: $about);
        $this->item($menu, 'Student Life', '/student-life', order: 3, parent: $about);

        $academics = $this->item($menu, 'Academics', '/courses', order: 2, isMega: true);
        $this->item($menu, 'Faculties', '/faculties', order: 0, parent: $academics);
        $this->item($menu, 'Departments', '/departments', order: 1, parent: $academics);
        $this->item($menu, 'Courses', '/courses', order: 2, parent: $academics);

        $admissions = $this->item($menu, 'Admissions', '/admissions', order: 3);
        $this->item($menu, 'How to Apply', '/how-to-apply', order: 0, parent: $admissions);
        $this->item($menu, 'Scholarships', '/scholarships', order: 1, parent: $admissions);
        $this->item($menu, 'International Students', '/international-students', order: 2, parent: $admissions);
        $this->item($menu, 'FAQ', '/faq', order: 3, parent: $admissions);
        $this->item($menu, 'Downloads', '/downloads', order: 4, parent: $admissions);

        $newsMedia = $this->item($menu, 'News & Media', '/news', order: 4);
        $this->item($menu, 'News', '/news', order: 0, parent: $newsMedia);
        $this->item($menu, 'Blog', '/blog', order: 1, parent: $newsMedia);
        $this->item($menu, 'Events', '/events', order: 2, parent: $newsMedia);
        $this->item($menu, 'Gallery', '/gallery', order: 3, parent: $newsMedia);

        $this->item($menu, 'Career', '/career', order: 5);
        $this->item($menu, 'Apply Now', '/apply', order: 6);
        $this->item($menu, 'Contact', '/contact', order: 7);
    }

    private function seedFooter(Menu $menu): void
    {
        $this->item($menu, 'About', '/about', order: 0);
        $this->item($menu, 'Courses', '/courses', order: 1);
        $this->item($menu, 'Admissions', '/admissions', order: 2);
        $this->item($menu, 'News', '/news', order: 3);
        $this->item($menu, 'FAQ', '/faq', order: 4);
        $this->item($menu, 'Downloads', '/downloads', order: 5);
        $this->item($menu, 'Apply Now', '/apply', order: 6);
        $this->item($menu, 'Contact', '/contact', order: 7);

        $this->item($menu, 'Privacy Policy', '/privacy-policy', order: 8);
        $this->item($menu, 'Terms & Conditions', '/terms', order: 9);
        $this->item($menu, 'Refund Policy', '/refund-policy', order: 10);
    }

    private function item(
        Menu $menu,
        string $label,
        string $customUrl,
        int $order,
        ?MenuItem $parent = null,
        bool $isMega = false
    ): MenuItem {
        return MenuItem::firstOrCreate(
            ['menu_id' => $menu->id, 'parent_id' => $parent?->id, 'label' => $label],
            ['custom_url' => $customUrl, 'order' => $order, 'is_mega_menu' => $isMega]
        );
    }
}
