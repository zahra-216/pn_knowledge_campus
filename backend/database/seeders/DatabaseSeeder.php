<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminUserSeeder::class,
            SettingSeeder::class,
            OfficeHourSeeder::class,
            MenuSeeder::class,
            PageSeeder::class,
            HomepageSectionSeeder::class,
            HeroSlideSeeder::class,
            TestimonialSeeder::class,
            PartnerCategorySeeder::class,
            PartnerSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            CourseLevelSeeder::class,
            CourseModeSeeder::class,
            CourseCategorySeeder::class,
            CourseSeeder::class,
            BlogCategorySeeder::class,
            TagSeeder::class,
            BlogPostSeeder::class,
            NewsCategorySeeder::class,
            NewsSeeder::class,
            EventSeeder::class,
            GalleryAlbumSeeder::class,
            FaqCategorySeeder::class,
            FaqSeeder::class,
            DownloadCategorySeeder::class,
            DownloadSeeder::class,
        ]);
    }
}
