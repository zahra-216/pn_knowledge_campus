-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping structure for table pn_knowledge_campus.applications
CREATE TABLE IF NOT EXISTS `applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `international_applicant` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('draft','submitted','under_review','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_application_number_unique` (`application_number`),
  KEY `applications_course_id_foreign` (`course_id`),
  KEY `applications_reviewed_by_foreign` (`reviewed_by`),
  KEY `applications_status_created_at_index` (`status`,`created_at`),
  KEY `applications_email_index` (`email`),
  CONSTRAINT `applications_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.applications: ~1 rows (approximately)
INSERT INTO `applications` (`id`, `application_number`, `course_id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `nationality`, `address`, `international_applicant`, `status`, `submitted_at`, `reviewed_by`, `reviewed_at`, `review_notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'PNKC-2026-058166', 4, 'Test', 'User', 'test@gmail.com', '0712345678', NULL, 'Sri Lankan', '25, Park Road, Colombo 05', 0, 'approved', '2026-08-17 03:27:11', 1, '2026-08-17 03:28:11', NULL, '2026-08-17 03:25:59', '2026-08-17 03:28:11', NULL);

-- Dumping structure for table pn_knowledge_campus.blog_categories
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_categories_name_unique` (`name`),
  UNIQUE KEY `blog_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.blog_categories: ~3 rows (approximately)
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Campus Life', 'campus-life', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Student Stories', 'student-stories', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Alumni Spotlight', 'alumni-spotlight', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.blog_posts
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `status` enum('draft','published','scheduled','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `views_count` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  KEY `blog_posts_author_id_foreign` (`author_id`),
  KEY `blog_posts_created_by_foreign` (`created_by`),
  KEY `blog_posts_updated_by_foreign` (`updated_by`),
  KEY `blog_posts_category_id_index` (`category_id`),
  KEY `blog_posts_status_published_at_index` (`status`,`published_at`),
  KEY `blog_posts_is_featured_index` (`is_featured`),
  FULLTEXT KEY `blog_posts_title_excerpt_fulltext` (`title`,`excerpt`),
  CONSTRAINT `blog_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `blog_posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blog_posts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blog_posts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.blog_posts: ~3 rows (approximately)
INSERT INTO `blog_posts` (`id`, `category_id`, `title`, `slug`, `excerpt`, `body`, `author_id`, `status`, `published_at`, `is_featured`, `views_count`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Welcome to the New Academic Year', 'welcome-to-the-new-academic-year', 'A short introduction to "Welcome to the New Academic Year".', '<p>This is the full body of <strong>Welcome to the New Academic Year</strong>.</p>', 1, 'published', '2026-08-06 09:53:11', 1, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(2, 2, 'Five Scholarships Every New Student Should Know About', 'five-scholarships-every-new-student-should-know-about', 'A short introduction to "Five Scholarships Every New Student Should Know About".', '<p>This is the full body of <strong>Five Scholarships Every New Student Should Know About</strong>.</p>', 1, 'published', '2026-07-20 09:53:11', 0, 1, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:38:45', NULL),
	(3, 3, 'Where Our Graduates Are Now', 'where-our-graduates-are-now', 'A short introduction to "Where Our Graduates Are Now".', '<p>This is the full body of <strong>Where Our Graduates Are Now</strong>.</p>', 1, 'published', '2026-07-13 09:53:11', 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL);

-- Dumping structure for table pn_knowledge_campus.branches
CREATE TABLE IF NOT EXISTS `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_head_office` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_created_by_foreign` (`created_by`),
  KEY `branches_updated_by_foreign` (`updated_by`),
  KEY `branches_is_active_order_index` (`is_active`,`order`),
  CONSTRAINT `branches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `branches_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.branches: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.cache: ~19 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('pnkc_cache_1009ba5745cff0156d9ba647b9fd2395', 'i:2;', 1787036308),
	('pnkc_cache_1009ba5745cff0156d9ba647b9fd2395:timer', 'i:1787036308;', 1787036308),
	('pnkc_cache_4a441a2a1feb832a3b684a3da33cad9b', 'i:1;', 1786687684),
	('pnkc_cache_4a441a2a1feb832a3b684a3da33cad9b:timer', 'i:1786687684;', 1786687684),
	('pnkc_cache_7b78d0e560ec5b340eee0be02051b674', 'i:1;', 1786687817),
	('pnkc_cache_7b78d0e560ec5b340eee0be02051b674:timer', 'i:1786687817;', 1786687817),
	('pnkc_cache_a75f3f172bfb296f2e10cbfc6dfc1883', 'i:1;', 1786692717),
	('pnkc_cache_a75f3f172bfb296f2e10cbfc6dfc1883:timer', 'i:1786692717;', 1786692717),
	('pnkc_cache_f1f70ec40aaa556905d4a030501c0ba4', 'i:1;', 1787037235),
	('pnkc_cache_f1f70ec40aaa556905d4a030501c0ba4:timer', 'i:1787037235;', 1787037235),
	('pnkc_cache_f96ca582c8a787ad61b182cfa981637c', 'i:4;', 1787028780),
	('pnkc_cache_f96ca582c8a787ad61b182cfa981637c:timer', 'i:1787028780;', 1787028780),
	('pnkc_cache_public.branches', 'a:0:{}', 1786959633),
	('pnkc_cache_public.homepage', 'a:12:{i:0;a:2:{s:11:"section_key";s:7:"welcome";s:7:"content";a:3:{s:7:"heading";N;s:4:"body";N;s:8:"media_id";N;}}i:1;a:2:{s:11:"section_key";s:4:"hero";s:5:"items";a:3:{i:0;a:11:{s:2:"id";i:1;s:5:"title";s:28:"Welcome to PNK Global Campus";s:8:"subtitle";s:61:"Building futures through knowledge, character, and community.";s:8:"cta_text";N;s:7:"cta_url";N;s:5:"order";i:0;s:9:"starts_at";N;s:7:"ends_at";N;s:9:"is_active";b:1;s:9:"image_url";s:57:"http://localhost:8000/storage/4/conversions/hero1-web.jpg";s:9:"thumb_url";s:59:"http://localhost:8000/storage/4/conversions/hero1-thumb.jpg";}i:1;a:11:{s:2:"id";i:2;s:5:"title";s:19:"Admissions Now Open";s:8:"subtitle";s:33:"Start your journey with us today.";s:8:"cta_text";N;s:7:"cta_url";N;s:5:"order";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:9:"is_active";b:1;s:9:"image_url";s:57:"http://localhost:8000/storage/5/conversions/hero2-web.jpg";s:9:"thumb_url";s:59:"http://localhost:8000/storage/5/conversions/hero2-thumb.jpg";}i:2;a:11:{s:2:"id";i:3;s:5:"title";s:31:"A Campus Built for Student Life";s:8:"subtitle";s:47:"Clubs, sports, and events beyond the classroom.";s:8:"cta_text";N;s:7:"cta_url";N;s:5:"order";i:2;s:9:"starts_at";N;s:7:"ends_at";N;s:9:"is_active";b:1;s:9:"image_url";s:57:"http://localhost:8000/storage/6/conversions/hero3-web.jpg";s:9:"thumb_url";s:59:"http://localhost:8000/storage/6/conversions/hero3-thumb.jpg";}}}i:2;a:2:{s:11:"section_key";s:16:"featured_courses";s:5:"items";a:6:{i:0;a:26:{s:2:"id";i:4;s:11:"course_name";s:29:"Diploma in Quantity Surveying";s:11:"course_code";s:14:"ENG-DIP-QS-001";s:4:"slug";s:29:"diploma-in-quantity-surveying";s:7:"faculty";a:3:{s:2:"id";i:2;s:4:"name";s:22:"Faculty of Engineering";s:4:"slug";s:22:"faculty-of-engineering";}s:10:"department";a:3:{s:2:"id";i:3;s:4:"name";s:31:"Department of Civil Engineering";s:4:"slug";s:31:"department-of-civil-engineering";}s:5:"level";a:2:{s:2:"id";i:2;s:4:"name";s:7:"Diploma";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:18;s:13:"duration_unit";s:5:"month";s:8:"duration";s:9:"18 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:117:"A practical diploma covering cost estimation, measurement, and contract administration for the construction industry.";s:11:"description";s:124:"<p>A practical diploma covering cost estimation, measurement, and contract administration for the construction industry.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:1;a:26:{s:2:"id";i:5;s:11:"course_name";s:36:"Diploma in Psychology and Counseling";s:11:"course_code";s:14:"HS-DIP-PSY-001";s:4:"slug";s:36:"diploma-in-psychology-and-counseling";s:7:"faculty";a:3:{s:2:"id";i:5;s:4:"name";s:26:"Faculty of Health Sciences";s:4:"slug";s:26:"faculty-of-health-sciences";}s:10:"department";a:3:{s:2:"id";i:8;s:4:"name";s:37:"Department of Psychology & Counseling";s:4:"slug";s:35:"department-of-psychology-counseling";}s:5:"level";a:2:{s:2:"id";i:2;s:4:"name";s:7:"Diploma";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:18;s:13:"duration_unit";s:5:"month";s:8:"duration";s:9:"18 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:125:"A foundational diploma in psychological principles and counseling practice for aspiring counselors and support professionals.";s:11:"description";s:132:"<p>A foundational diploma in psychological principles and counseling practice for aspiring counselors and support professionals.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:2;a:26:{s:2:"id";i:6;s:11:"course_name";s:53:"Diploma in Early Childhood Care and Primary Education";s:11:"course_code";s:15:"EDU-DIP-ECE-001";s:4:"slug";s:53:"diploma-in-early-childhood-care-and-primary-education";s:7:"faculty";a:3:{s:2:"id";i:6;s:4:"name";s:20:"Faculty of Education";s:4:"slug";s:20:"faculty-of-education";}s:10:"department";a:3:{s:2:"id";i:9;s:4:"name";s:39:"Department of Early Childhood Education";s:4:"slug";s:39:"department-of-early-childhood-education";}s:5:"level";a:2:{s:2:"id";i:2;s:4:"name";s:7:"Diploma";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:18;s:13:"duration_unit";s:5:"month";s:8:"duration";s:9:"18 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:88:"Prepares students for careers in early childhood development and primary-level teaching.";s:11:"description";s:95:"<p>Prepares students for careers in early childhood development and primary-level teaching.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:51.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:3;a:26:{s:2:"id";i:7;s:11:"course_name";s:55:"Certificate in Mechanical and Electrical Plumbing (MEP)";s:11:"course_code";s:16:"ENG-CERT-MEP-001";s:4:"slug";s:53:"certificate-in-mechanical-and-electrical-plumbing-mep";s:7:"faculty";a:3:{s:2:"id";i:2;s:4:"name";s:22:"Faculty of Engineering";s:4:"slug";s:22:"faculty-of-engineering";}s:10:"department";a:3:{s:2:"id";i:7;s:4:"name";s:36:"Department of Mechanical Engineering";s:4:"slug";s:36:"department-of-mechanical-engineering";}s:5:"level";a:2:{s:2:"id";i:1;s:4:"name";s:11:"Certificate";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:6;s:13:"duration_unit";s:5:"month";s:8:"duration";s:8:"6 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:101:"Hands-on certificate covering mechanical, electrical, and plumbing systems used in building services.";s:11:"description";s:108:"<p>Hands-on certificate covering mechanical, electrical, and plumbing systems used in building services.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:52.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:52.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:4;a:26:{s:2:"id";i:8;s:11:"course_name";s:55:"Certificate in Information and Communication Technology";s:11:"course_code";s:15:"CS-CERT-ICT-001";s:4:"slug";s:55:"certificate-in-information-and-communication-technology";s:7:"faculty";a:3:{s:2:"id";i:3;s:4:"name";s:20:"Faculty of Computing";s:4:"slug";s:20:"faculty-of-computing";}s:10:"department";a:3:{s:2:"id";i:5;s:4:"name";s:30:"Department of Computer Science";s:4:"slug";s:30:"department-of-computer-science";}s:5:"level";a:2:{s:2:"id";i:1;s:4:"name";s:11:"Certificate";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:6;s:13:"duration_unit";s:5:"month";s:8:"duration";s:8:"6 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:102:"An introductory ICT certificate covering computer literacy, office applications, and basic networking.";s:11:"description";s:109:"<p>An introductory ICT certificate covering computer literacy, office applications, and basic networking.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:52.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:52.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:5;a:26:{s:2:"id";i:9;s:11:"course_name";s:30:"Certificate in Second Language";s:11:"course_code";s:18:"ARTS-CERT-LANG-001";s:4:"slug";s:30:"certificate-in-second-language";s:7:"faculty";a:3:{s:2:"id";i:7;s:4:"name";s:15:"Faculty of Arts";s:4:"slug";s:15:"faculty-of-arts";}s:10:"department";a:3:{s:2:"id";i:10;s:4:"name";s:23:"Department of Languages";s:4:"slug";s:23:"department-of-languages";}s:5:"level";a:2:{s:2:"id";i:1;s:4:"name";s:11:"Certificate";}s:4:"mode";a:2:{s:2:"id";i:1;s:4:"name";s:9:"Full-Time";}s:8:"category";N;s:14:"duration_value";i:6;s:13:"duration_unit";s:5:"month";s:8:"duration";s:8:"6 Months";s:5:"price";a:3:{s:6:"amount";d:0;s:8:"currency";s:3:"LKR";s:15:"discount_amount";N;}s:8:"overview";s:98:"A practical language certificate building conversational and written fluency in a second language.";s:11:"description";s:105:"<p>A practical language certificate building conversational and written fluency in a second language.</p>";s:18:"entry_requirements";N;s:17:"learning_outcomes";N;s:20:"career_opportunities";N;s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:54.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:5:"order";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:9:"downloads";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:31:54.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}}}i:3;a:2:{s:11:"section_key";s:9:"faculties";s:5:"items";a:5:{i:0;a:17:{s:2:"id";i:5;s:4:"name";s:26:"Faculty of Health Sciences";s:4:"slug";s:26:"faculty-of-health-sciences";s:17:"short_description";s:113:"Preparing skilled, compassionate healthcare professionals through evidence-based education and clinical training.";s:11:"description";s:526:"The Faculty of Health Sciences equips students with the knowledge, clinical skills, and ethical grounding needed for careers across the healthcare sector. Programmes combine rigorous theoretical foundations with hands-on clinical placements, simulation labs, and community health experience, preparing graduates to deliver safe, patient-centred care from day one. Students are mentored by experienced practitioners and engage with real-world case studies, public health challenges, and the latest advances in medical practice.";s:9:"dean_name";N;s:10:"dean_title";N;s:12:"dean_message";N;s:5:"order";i:0;s:6:"status";s:9:"published";s:8:"icon_url";N;s:10:"banner_url";s:78:"http://localhost:8000/storage/10/conversions/faculty-of-health-science-web.jpg";s:14:"dean_photo_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:11:"departments";a:0:{}s:7:"courses";a:0:{}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:30:39.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:1;a:17:{s:2:"id";i:6;s:4:"name";s:20:"Faculty of Education";s:4:"slug";s:20:"faculty-of-education";s:17:"short_description";s:73:"Shaping the next generation of educators, trainers, and academic leaders.";s:11:"description";s:444:"The Faculty of Education trains future teachers, trainers, and education administrators to design meaningful learning experiences across all levels of schooling. The curriculum blends pedagogy, educational psychology, curriculum design, and classroom technology with supervised teaching practice in real school settings. Graduates leave equipped not only to teach, but to lead, innovate, and advocate for inclusive, effective education systems.";s:9:"dean_name";N;s:10:"dean_title";N;s:12:"dean_message";N;s:5:"order";i:0;s:6:"status";s:9:"published";s:8:"icon_url";N;s:10:"banner_url";s:73:"http://localhost:8000/storage/14/conversions/faculty-of-education-web.jpg";s:14:"dean_photo_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:11:"departments";a:0:{}s:7:"courses";a:0:{}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:30:39.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:2;a:17:{s:2:"id";i:7;s:4:"name";s:15:"Faculty of Arts";s:4:"slug";s:15:"faculty-of-arts";s:17:"short_description";s:117:"Cultivating creativity, critical thinking, and cultural understanding across the humanities and creative disciplines.";s:11:"description";s:494:"The Faculty of Arts offers a broad, interdisciplinary education spanning humanities, languages, media, and creative practice. Students develop strong analytical and communication skills while exploring literature, history, culture, and the arts, guided by faculty who encourage independent thought and creative expression. Graduates go on to careers in media, publishing, communications, cultural institutions, and beyond - equipped with the versatile, transferable skills employers value most.";s:9:"dean_name";N;s:10:"dean_title";N;s:12:"dean_message";N;s:5:"order";i:0;s:6:"status";s:9:"published";s:8:"icon_url";N;s:10:"banner_url";s:68:"http://localhost:8000/storage/15/conversions/faculty-of-arts-web.jpg";s:14:"dean_photo_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:11:"departments";a:0:{}s:7:"courses";a:0:{}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-14 06:30:39.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:3;a:17:{s:2:"id";i:2;s:4:"name";s:22:"Faculty of Engineering";s:4:"slug";s:22:"faculty-of-engineering";s:17:"short_description";s:70:"Practical, hands-on engineering education across multiple disciplines.";s:11:"description";s:164:"The Faculty of Engineering delivers rigorous programmes in civil, mechanical, electrical, and computer engineering, backed by modern labs and industry partnerships.";s:9:"dean_name";s:18:"Prof. Samuel Owusu";s:10:"dean_title";s:28:"Dean, Faculty of Engineering";s:12:"dean_message";s:62:"We prepare engineers who can solve real problems from day one.";s:5:"order";i:1;s:6:"status";s:9:"published";s:8:"icon_url";N;s:10:"banner_url";s:75:"http://localhost:8000/storage/16/conversions/faculty-of-engineering-web.jpg";s:14:"dean_photo_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:11:"departments";a:0:{}s:7:"courses";a:0:{}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:10.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:4;a:17:{s:2:"id";i:3;s:4:"name";s:20:"Faculty of Computing";s:4:"slug";s:20:"faculty-of-computing";s:17:"short_description";s:99:"Building the next generation of tech talent through hands-on learning and industry-relevant skills.";s:11:"description";s:481:"The Faculty of Computing prepares students for careers at the forefront of technology, with programmes spanning software engineering, computer science fundamentals, and data science. The curriculum balances strong theoretical grounding in algorithms, systems, and data with extensive hands-on coding, project work, and exposure to current industry tools and practices. Graduates leave ready to build, secure, and scale the software and data systems driving today\'s digital economy.";s:9:"dean_name";s:16:"Dr. Grace Mensah";s:10:"dean_title";s:26:"Dean, Faculty of Computing";s:12:"dean_message";s:101:"Technology moves fast, and so do we — our curriculum evolves every year to keep pace with industry.";s:5:"order";i:2;s:6:"status";s:9:"published";s:8:"icon_url";N;s:10:"banner_url";s:73:"http://localhost:8000/storage/18/conversions/faculty-of-computing-web.jpg";s:14:"dean_photo_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:11:"departments";a:0:{}s:7:"courses";a:0:{}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:10.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}}}i:4;a:2:{s:11:"section_key";s:13:"why_choose_us";s:5:"items";a:0:{}}i:5;a:2:{s:11:"section_key";s:10:"statistics";s:5:"items";a:0:{}}i:6;a:2:{s:11:"section_key";s:12:"testimonials";s:5:"items";a:3:{i:0;a:10:{s:2:"id";i:1;s:4:"name";s:12:"Aisha Rahman";s:10:"role_title";s:18:"BSc Graduate, 2024";s:9:"course_id";N;s:7:"content";s:117:"The faculty here pushed me to think beyond the syllabus. I left more confident and more capable than I ever expected.";s:6:"rating";i:5;s:11:"is_featured";b:1;s:5:"order";i:0;s:9:"is_active";b:1;s:9:"photo_url";N;}i:1;a:10:{s:2:"id";i:2;s:4:"name";s:11:"Daniel Osei";s:10:"role_title";s:18:"MSc Graduate, 2023";s:9:"course_id";N;s:7:"content";s:89:"Small class sizes meant real mentorship, not just lectures. That made all the difference.";s:6:"rating";i:5;s:11:"is_featured";b:1;s:5:"order";i:1;s:9:"is_active";b:1;s:9:"photo_url";N;}i:2;a:10:{s:2:"id";i:3;s:4:"name";s:10:"Priya Nair";s:10:"role_title";s:15:"Current Student";s:9:"course_id";N;s:7:"content";s:67:"From day one, campus life felt like a community, not just a school.";s:6:"rating";i:4;s:11:"is_featured";b:1;s:5:"order";i:2;s:9:"is_active";b:1;s:9:"photo_url";N;}}}i:7;a:2:{s:11:"section_key";s:8:"partners";s:5:"items";a:4:{i:0;a:6:{s:2:"id";i:1;s:4:"name";s:28:"National Accreditation Board";s:3:"url";N;s:5:"order";i:0;s:9:"is_active";b:1;s:8:"logo_url";N;}i:1;a:6:{s:2:"id";i:2;s:4:"name";s:21:"Ministry of Education";s:3:"url";N;s:5:"order";i:1;s:9:"is_active";b:1;s:8:"logo_url";N;}i:2;a:6:{s:2:"id";i:3;s:4:"name";s:32:"International Education Alliance";s:3:"url";s:19:"https://example.org";s:5:"order";i:2;s:9:"is_active";b:1;s:8:"logo_url";N;}i:3;a:6:{s:2:"id";i:4;s:4:"name";s:18:"TechCorp Solutions";s:3:"url";s:19:"https://example.org";s:5:"order";i:3;s:9:"is_active";b:1;s:8:"logo_url";N;}}}i:8;a:2:{s:11:"section_key";s:11:"latest_news";s:5:"items";a:3:{i:0;a:12:{s:2:"id";i:3;s:5:"title";s:67:"Faculty of Engineering Students Place First at Robotics Competition";s:4:"slug";s:67:"faculty-of-engineering-students-place-first-at-robotics-competition";s:7:"excerpt";s:94:"A short introduction to "Faculty of Engineering Students Place First at Robotics Competition".";s:4:"body";s:117:"<p>This is the full body of <strong>Faculty of Engineering Students Place First at Robotics Competition</strong>.</p>";s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-07-28 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:0;s:11:"views_count";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:1;a:12:{s:2:"id";i:2;s:5:"title";s:49:"New Partnership With Regional Hospitals Announced";s:4:"slug";s:49:"new-partnership-with-regional-hospitals-announced";s:7:"excerpt";s:76:"A short introduction to "New Partnership With Regional Hospitals Announced".";s:4:"body";s:99:"<p>This is the full body of <strong>New Partnership With Regional Hospitals Announced</strong>.</p>";s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-07-18 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:0;s:11:"views_count";i:1;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:2;a:12:{s:2:"id";i:1;s:5:"title";s:37:"Campus Wins National Innovation Award";s:4:"slug";s:37:"campus-wins-national-innovation-award";s:7:"excerpt";s:64:"A short introduction to "Campus Wins National Innovation Award".";s:4:"body";s:87:"<p>This is the full body of <strong>Campus Wins National Innovation Award</strong>.</p>";s:6:"status";s:9:"published";s:12:"published_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-07-16 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_featured";b:1;s:11:"views_count";i:0;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}}}i:9;a:2:{s:11:"section_key";s:15:"upcoming_events";s:5:"items";a:2:{i:0;a:15:{s:2:"id";i:1;s:5:"title";s:13:"Open Day 2026";s:4:"slug";s:13:"open-day-2026";s:5:"venue";s:22:"Main Campus Auditorium";s:9:"is_online";b:0;s:9:"starts_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-24 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:7:"ends_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-24 13:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_upcoming";b:1;s:11:"description";s:26:"Details for Open Day 2026.";s:16:"registration_url";N;s:6:"status";s:9:"published";s:12:"published_at";N;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}i:1;a:15:{s:2:"id";i:2;s:5:"title";s:21:"Annual Tech Symposium";s:4:"slug";s:21:"annual-tech-symposium";s:5:"venue";N;s:9:"is_online";b:1;s:9:"starts_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-09-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:7:"ends_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-09-10 15:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:11:"is_upcoming";b:1;s:11:"description";s:34:"Details for Annual Tech Symposium.";s:16:"registration_url";N;s:6:"status";s:9:"published";s:12:"published_at";N;s:18:"featured_image_url";N;s:7:"gallery";O:71:"Spatie\\MediaLibrary\\MediaCollections\\Models\\Collections\\MediaCollection":4:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;s:14:"collectionName";N;s:13:"formFieldName";N;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:11.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}}}i:10;a:2:{s:11:"section_key";s:3:"cta";s:7:"content";a:4:{s:7:"heading";N;s:4:"body";N;s:12:"button_label";N;s:10:"button_url";N;}}i:11;a:2:{s:11:"section_key";s:14:"footer_widgets";s:5:"items";a:0:{}}}', 1787036547),
	('pnkc_cache_public.menu.footer', 'a:4:{s:2:"id";i:2;s:4:"name";s:6:"footer";s:5:"items";O:58:"Illuminate\\Http\\Resources\\Json\\AnonymousResourceCollection":8:{s:8:"resource";O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:11:{i:0;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:25;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:5:"About";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/about";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:25;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:5:"About";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/about";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:1;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:26;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:7:"Courses";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:26;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:7:"Courses";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:2;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:27;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:10:"Admissions";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:11:"/admissions";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:27;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:10:"Admissions";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:11:"/admissions";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:3;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:28;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:4:"News";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:3;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:28;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:4:"News";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:3;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:4;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:29;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:3:"FAQ";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:4:"/faq";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:4;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:29;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:3:"FAQ";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:4:"/faq";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:4;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:5;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:30;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:9:"Downloads";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:10:"/downloads";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:5;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:30;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:9:"Downloads";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:10:"/downloads";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:5;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:6;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:31;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:9:"Apply Now";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/apply";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:6;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:31;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:9:"Apply Now";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/apply";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:6;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:7;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:32;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:7:"Contact";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/contact";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:7;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:32;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:7:"Contact";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/contact";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:7;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:8;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:33;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:14:"Privacy Policy";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:15:"/privacy-policy";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:8;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:33;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:14:"Privacy Policy";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:15:"/privacy-policy";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:8;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:9;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:34;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:18:"Terms & Conditions";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/terms";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:9;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:34;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:18:"Terms & Conditions";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/terms";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:9;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:10;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:35;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:13:"Refund Policy";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:14:"/refund-policy";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:10;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:11:"\0*\0original";a:20:{s:2:"id";i:35;s:7:"menu_id";i:2;s:9:"parent_id";N;s:5:"label";s:13:"Refund Policy";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:14:"/refund-policy";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:10;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-10 09:53:10";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}s:4:"with";a:0:{}s:10:"additional";a:0:{}s:8:"collects";s:35:"App\\Http\\Resources\\MenuItemResource";s:10:"collection";r:5;s:29:"\0*\0preserveAllQueryParameters";b:0;s:18:"\0*\0queryParameters";N;s:12:"preserveKeys";b:0;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:10.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}', 1787036549),
	('pnkc_cache_public.menu.header', 'a:4:{s:2:"id";i:1;s:4:"name";s:6:"header";s:5:"items";O:58:"Illuminate\\Http\\Resources\\Json\\AnonymousResourceCollection":8:{s:8:"resource";O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:7:{i:0;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:1;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:4:"Home";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:1:"/";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:1;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:4:"Home";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:1:"/";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:1;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:2;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:5:"About";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/about";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:2;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:5:"About";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/about";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:2;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:36;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:12:"Student Life";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:1:"#";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:36:22";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:36;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:12:"Student Life";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:1:"#";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:36:22";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:3:{i:0;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:37;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:12:"Registration";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/apply";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";i:1;s:10:"created_at";s:19:"2026-08-18 04:37:56";s:10:"updated_at";s:19:"2026-08-18 04:44:50";}s:11:"\0*\0original";a:20:{s:2:"id";i:37;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:12:"Registration";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:6:"/apply";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";i:1;s:10:"created_at";s:19:"2026-08-18 04:37:56";s:10:"updated_at";s:19:"2026-08-18 04:44:50";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:1;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:38;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:11:"Examination";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:25:"/student-life/examination";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:38:23";s:10:"updated_at";s:19:"2026-08-18 04:38:23";}s:11:"\0*\0original";a:20:{s:2:"id";i:38;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:11:"Examination";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:25:"/student-life/examination";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:38:23";s:10:"updated_at";s:19:"2026-08-18 04:38:23";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:2;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:39;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:24:"Certificate Verification";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:38:"/student-life/certificate-verification";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:38:52";s:10:"updated_at";s:19:"2026-08-18 04:38:52";}s:11:"\0*\0original";a:20:{s:2:"id";i:39;s:7:"menu_id";i:1;s:9:"parent_id";i:36;s:5:"label";s:24:"Certificate Verification";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:38:"/student-life/certificate-verification";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";i:1;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-18 04:38:52";s:10:"updated_at";s:19:"2026-08-18 04:38:52";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:3;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:7;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:9:"Academics";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:1;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:7;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:9:"Academics";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:1;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:3:{i:0;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:8;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:9:"Faculties";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:10:"/faculties";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:8;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:9:"Faculties";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:10:"/faculties";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:1;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:9;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:11:"Departments";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:12:"/departments";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:9;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:11:"Departments";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:12:"/departments";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:2;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:10;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:7:"Courses";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:10;s:7:"menu_id";i:1;s:9:"parent_id";i:7;s:5:"label";s:7:"Courses";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/courses";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:4;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:17;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:12:"News & Media";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:4;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:17;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:12:"News & Media";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:4;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:4:{i:0;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:18;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:4:"News";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:18;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:4:"News";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/news";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:0;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:1;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:19;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:4:"Blog";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/blog";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:19;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:4:"Blog";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:5:"/blog";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:1;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:2;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:20;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:6:"Events";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:7:"/events";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:20;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:6:"Events";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:7:"/events";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:2;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}i:3;O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:21;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:7:"Gallery";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/gallery";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:3;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:21;s:7:"menu_id";i:1;s:9:"parent_id";i:17;s:5:"label";s:7:"Gallery";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/gallery";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:3;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:5;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:22;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:6:"Career";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:7:"/career";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:5;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:22;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:6:"Career";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:7:"/career";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:5;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}i:6;O:35:"App\\Http\\Resources\\MenuItemResource":3:{s:8:"resource";O:19:"App\\Models\\MenuItem":33:{s:13:"\0*\0connection";s:5:"mysql";s:8:"\0*\0table";s:10:"menu_items";s:13:"\0*\0primaryKey";s:2:"id";s:10:"\0*\0keyType";s:3:"int";s:12:"incrementing";b:1;s:7:"\0*\0with";a:0:{}s:12:"\0*\0withCount";a:0:{}s:19:"preventsLazyLoading";b:0;s:10:"\0*\0perPage";i:15;s:6:"exists";b:1;s:18:"wasRecentlyCreated";b:0;s:28:"\0*\0escapeWhenCastingToString";b:0;s:13:"\0*\0attributes";a:20:{s:2:"id";i:24;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:7:"Contact";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/contact";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:7;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:11:"\0*\0original";a:20:{s:2:"id";i:24;s:7:"menu_id";i:1;s:9:"parent_id";N;s:5:"label";s:7:"Contact";s:13:"linkable_type";N;s:11:"linkable_id";N;s:10:"custom_url";s:8:"/contact";s:11:"description";N;s:4:"icon";N;s:12:"is_mega_menu";i:0;s:15:"open_in_new_tab";i:0;s:5:"order";i:7;s:9:"is_active";i:1;s:9:"starts_at";N;s:7:"ends_at";N;s:10:"visible_on";s:4:"both";s:10:"created_by";N;s:10:"updated_by";N;s:10:"created_at";s:19:"2026-08-10 09:53:10";s:10:"updated_at";s:19:"2026-08-18 04:37:30";}s:10:"\0*\0changes";a:0:{}s:11:"\0*\0previous";a:0:{}s:8:"\0*\0casts";a:5:{s:12:"is_mega_menu";s:7:"boolean";s:15:"open_in_new_tab";s:7:"boolean";s:9:"is_active";s:7:"boolean";s:9:"starts_at";s:8:"datetime";s:7:"ends_at";s:8:"datetime";}s:17:"\0*\0classCastCache";a:0:{}s:21:"\0*\0attributeCastCache";a:0:{}s:13:"\0*\0dateFormat";N;s:10:"\0*\0appends";a:0:{}s:19:"\0*\0dispatchesEvents";a:0:{}s:14:"\0*\0observables";a:0:{}s:12:"\0*\0relations";a:1:{s:8:"children";O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}}s:10:"\0*\0touches";a:0:{}s:27:"\0*\0relationAutoloadCallback";N;s:26:"\0*\0relationAutoloadContext";N;s:10:"timestamps";b:1;s:13:"usesUniqueIds";b:0;s:9:"\0*\0hidden";a:0:{}s:10:"\0*\0visible";a:0:{}s:11:"\0*\0fillable";a:15:{i:0;s:7:"menu_id";i:1;s:9:"parent_id";i:2;s:5:"label";i:3;s:13:"linkable_type";i:4;s:11:"linkable_id";i:5;s:10:"custom_url";i:6;s:11:"description";i:7;s:4:"icon";i:8;s:12:"is_mega_menu";i:9;s:15:"open_in_new_tab";i:10;s:5:"order";i:11;s:9:"is_active";i:12;s:9:"starts_at";i:13;s:7:"ends_at";i:14;s:10:"visible_on";}s:10:"\0*\0guarded";a:1:{i:0;s:1:"*";}}s:4:"with";a:0:{}s:10:"additional";a:0:{}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}s:4:"with";a:0:{}s:10:"additional";a:0:{}s:8:"collects";s:35:"App\\Http\\Resources\\MenuItemResource";s:10:"collection";r:5;s:29:"\0*\0preserveAllQueryParameters";b:0;s:18:"\0*\0queryParameters";N;s:12:"preserveKeys";b:0;}s:10:"created_at";O:25:"Illuminate\\Support\\Carbon":3:{s:4:"date";s:26:"2026-08-10 09:53:10.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}}', 1787036549),
	('pnkc_cache_public.office_hours', 'a:7:{i:0;a:6:{s:3:"day";s:6:"monday";s:7:"is_open";b:1;s:8:"opens_at";s:5:"08:30";s:9:"closes_at";s:5:"17:00";s:4:"note";N;s:5:"order";i:0;}i:1;a:6:{s:3:"day";s:7:"tuesday";s:7:"is_open";b:1;s:8:"opens_at";s:5:"08:30";s:9:"closes_at";s:5:"17:00";s:4:"note";N;s:5:"order";i:1;}i:2;a:6:{s:3:"day";s:9:"wednesday";s:7:"is_open";b:1;s:8:"opens_at";s:5:"08:30";s:9:"closes_at";s:5:"17:00";s:4:"note";N;s:5:"order";i:2;}i:3;a:6:{s:3:"day";s:8:"thursday";s:7:"is_open";b:1;s:8:"opens_at";s:5:"08:30";s:9:"closes_at";s:5:"17:00";s:4:"note";N;s:5:"order";i:3;}i:4;a:6:{s:3:"day";s:6:"friday";s:7:"is_open";b:1;s:8:"opens_at";s:5:"08:30";s:9:"closes_at";s:5:"17:00";s:4:"note";N;s:5:"order";i:4;}i:5;a:6:{s:3:"day";s:8:"saturday";s:7:"is_open";b:0;s:8:"opens_at";N;s:9:"closes_at";N;s:4:"note";N;s:5:"order";i:5;}i:6;a:6:{s:3:"day";s:6:"sunday";s:7:"is_open";b:0;s:8:"opens_at";N;s:9:"closes_at";N;s:4:"note";N;s:5:"order";i:6;}}', 1786959633),
	('pnkc_cache_public.settings', 'O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:35:{s:11:"campus_name";N;s:17:"campus_short_name";N;s:14:"campus_tagline";N;s:19:"registration_number";N;s:20:"accreditation_number";N;s:13:"contact_email";s:39:"info@pnkgcampus.com, pnkgc.sl@gmail.com";s:13:"contact_phone";s:22:"0771981327, 0771981447";s:15:"contact_address";s:64:"635, Masjithul Islam Nagar, Maruthamunai-03, Kalmunai, Sri Lanka";s:16:"admissions_email";N;s:16:"admissions_phone";N;s:21:"google_maps_embed_url";N;s:19:"google_maps_api_key";N;s:13:"logo_media_id";s:1:"3";s:16:"favicon_media_id";s:1:"3";s:18:"header_logo_height";N;s:18:"footer_logo_height";N;s:11:"footer_text";N;s:16:"footer_copyright";N;s:14:"ga_tracking_id";N;s:16:"gtm_container_id";N;s:8:"site_url";N;s:18:"default_meta_title";N;s:24:"default_meta_description";N;s:16:"default_keywords";N;s:25:"default_og_image_media_id";N;s:15:"welcome_heading";N;s:12:"welcome_body";N;s:16:"welcome_media_id";N;s:19:"why_choose_us_items";N;s:16:"statistics_items";N;s:11:"cta_heading";N;s:8:"cta_body";N;s:16:"cta_button_label";N;s:14:"cta_button_url";N;s:14:"footer_widgets";N;}s:28:"\0*\0escapeWhenCastingToString";b:0;}', 1787036549),
	('pnkc_cache_public.social_links', 'a:0:{}', 1786945590),
	('pnkc_cache_public.social-links', 'O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}', 1787036549),
	('pnkc_cache_spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:83:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:10:"users.view";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:12:"users.create";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:10:"users.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:12:"users.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:10:"roles.view";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:12:"roles.create";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:10:"roles.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:12:"roles.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:14:"dashboard.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:13:"settings.view";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:13:"settings.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:1:{i:0;i:1;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:10:"media.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:12:"media.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:10:"media.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:12:"media.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:8:"seo.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:8:"seo.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:10:"menus.view";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:10:"menus.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:10:"pages.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:12:"pages.create";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:10:"pages.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:12:"pages.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:13:"pages.publish";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:13:"homepage.view";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:13:"homepage.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:16:"hero_slides.view";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:18:"hero_slides.create";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:16:"hero_slides.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:18:"hero_slides.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:17:"testimonials.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:19:"testimonials.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:17:"testimonials.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:19:"testimonials.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:13:"partners.view";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:15:"partners.create";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:13:"partners.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:15:"partners.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:14:"faculties.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:16:"faculties.create";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:14:"faculties.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:16:"faculties.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:16:"departments.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:18:"departments.create";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:16:"departments.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:18:"departments.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:12:"courses.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:14:"courses.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:12:"courses.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:14:"courses.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:15:"courses.publish";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:9:"blog.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:11:"blog.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:53;a:4:{s:1:"a";i:54;s:1:"b";s:9:"blog.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:54;a:4:{s:1:"a";i:55;s:1:"b";s:11:"blog.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:"a";i:56;s:1:"b";s:12:"blog.publish";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:56;a:4:{s:1:"a";i:57;s:1:"b";s:9:"news.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:57;a:4:{s:1:"a";i:58;s:1:"b";s:11:"news.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:58;a:4:{s:1:"a";i:59;s:1:"b";s:9:"news.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:59;a:4:{s:1:"a";i:60;s:1:"b";s:11:"news.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:60;a:4:{s:1:"a";i:61;s:1:"b";s:12:"news.publish";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:61;a:4:{s:1:"a";i:62;s:1:"b";s:11:"events.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:62;a:4:{s:1:"a";i:63;s:1:"b";s:13:"events.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:63;a:4:{s:1:"a";i:64;s:1:"b";s:11:"events.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:64;a:4:{s:1:"a";i:65;s:1:"b";s:13:"events.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:65;a:4:{s:1:"a";i:66;s:1:"b";s:12:"gallery.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:66;a:4:{s:1:"a";i:67;s:1:"b";s:14:"gallery.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:67;a:4:{s:1:"a";i:68;s:1:"b";s:12:"gallery.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:68;a:4:{s:1:"a";i:69;s:1:"b";s:14:"gallery.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:69;a:4:{s:1:"a";i:70;s:1:"b";s:8:"faq.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:70;a:4:{s:1:"a";i:71;s:1:"b";s:10:"faq.create";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:71;a:4:{s:1:"a";i:72;s:1:"b";s:8:"faq.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:72;a:4:{s:1:"a";i:73;s:1:"b";s:10:"faq.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:73;a:4:{s:1:"a";i:74;s:1:"b";s:14:"downloads.view";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:74;a:4:{s:1:"a";i:75;s:1:"b";s:16:"downloads.create";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:75;a:4:{s:1:"a";i:76;s:1:"b";s:14:"downloads.edit";s:1:"c";s:7:"sanctum";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:76;a:4:{s:1:"a";i:77;s:1:"b";s:16:"downloads.delete";s:1:"c";s:7:"sanctum";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:77;a:4:{s:1:"a";i:78;s:1:"b";s:17:"applications.view";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:78;a:4:{s:1:"a";i:79;s:1:"b";s:19:"applications.review";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:79;a:4:{s:1:"a";i:80;s:1:"b";s:19:"applications.export";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:80;a:4:{s:1:"a";i:81;s:1:"b";s:14:"inquiries.view";s:1:"c";s:7:"sanctum";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;}}i:81;a:4:{s:1:"a";i:82;s:1:"b";s:16:"inquiries.manage";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:82;a:4:{s:1:"a";i:83;s:1:"b";s:16:"inquiries.export";s:1:"c";s:7:"sanctum";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}}s:5:"roles";a:5:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:11:"Super Admin";s:1:"c";s:7:"sanctum";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:13:"Administrator";s:1:"c";s:7:"sanctum";}i:2;a:3:{s:1:"a";i:3;s:1:"b";s:14:"Content Editor";s:1:"c";s:7:"sanctum";}i:3;a:3:{s:1:"a";i:4;s:1:"b";s:9:"Marketing";s:1:"c";s:7:"sanctum";}i:4;a:3:{s:1:"a";i:5;s:1:"b";s:10:"Admissions";s:1:"c";s:7:"sanctum";}}}', 1787122652);

-- Dumping structure for table pn_knowledge_campus.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.cache_locks: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `level_id` bigint unsigned NOT NULL,
  `mode_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `course_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_value` smallint NOT NULL,
  `duration_unit` enum('day','week','month','year') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `price_currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `overview` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_requirements` longtext COLLATE utf8mb4_unicode_ci,
  `learning_outcomes` longtext COLLATE utf8mb4_unicode_ci,
  `career_opportunities` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','scheduled','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_course_code_unique` (`course_code`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_category_id_foreign` (`category_id`),
  KEY `courses_created_by_foreign` (`created_by`),
  KEY `courses_updated_by_foreign` (`updated_by`),
  KEY `courses_faculty_id_index` (`faculty_id`),
  KEY `courses_department_id_index` (`department_id`),
  KEY `courses_level_id_index` (`level_id`),
  KEY `courses_mode_id_index` (`mode_id`),
  KEY `courses_faculty_id_department_id_status_index` (`faculty_id`,`department_id`,`status`),
  KEY `courses_is_featured_index` (`is_featured`),
  FULLTEXT KEY `courses_course_name_overview_fulltext` (`course_name`,`overview`),
  CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `courses_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `courses_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `course_levels` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `courses_mode_id_foreign` FOREIGN KEY (`mode_id`) REFERENCES `course_modes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `courses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.courses: ~9 rows (approximately)
INSERT INTO `courses` (`id`, `faculty_id`, `department_id`, `level_id`, `mode_id`, `category_id`, `course_name`, `course_code`, `slug`, `duration_value`, `duration_unit`, `price`, `discount_price`, `price_currency`, `overview`, `description`, `entry_requirements`, `learning_outcomes`, `career_opportunities`, `status`, `published_at`, `is_featured`, `order`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 3, 5, 3, 1, 5, 'BSc (Hons) Computer Science', 'CS-BSC-001', 'bsc-hons-computer-science', 3, 'year', 4500.00, NULL, 'LKR', 'A comprehensive undergraduate programme in modern software development, algorithms, and systems design.', '<p>A comprehensive undergraduate programme in modern software development, algorithms, and systems design.</p>', NULL, NULL, NULL, 'published', '2026-08-10 09:53:11', 1, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-14 00:55:31', '2026-08-14 00:55:31'),
	(2, 3, 6, 3, 1, 5, 'BSc (Hons) Data Science', 'CS-BSC-002', 'bsc-hons-data-science', 3, 'year', 4800.00, NULL, 'LKR', 'An in-demand programme covering statistics, machine learning, and data engineering.', '<p>An in-demand programme covering statistics, machine learning, and data engineering.</p>', NULL, NULL, NULL, 'published', '2026-08-10 09:53:11', 1, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-14 00:55:44', '2026-08-14 00:55:44'),
	(3, 1, 1, 2, 2, 1, 'Diploma in Accounting & Finance', 'BUS-DIP-001', 'diploma-in-accounting-finance', 18, 'month', 2200.00, 1900.00, 'LKR', 'A part-time diploma for working professionals looking to build a career in accounting and finance.', '<p>A part-time diploma for working professionals looking to build a career in accounting and finance.</p>', NULL, NULL, NULL, 'published', '2026-08-10 09:53:11', 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-14 00:55:50', '2026-08-14 00:55:50'),
	(4, 2, 3, 2, 1, NULL, 'Diploma in Quantity Surveying', 'ENG-DIP-QS-001', 'diploma-in-quantity-surveying', 18, 'month', 0.00, NULL, 'LKR', 'A practical diploma covering cost estimation, measurement, and contract administration for the construction industry.', '<p>A practical diploma covering cost estimation, measurement, and contract administration for the construction industry.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:51', 1, 0, NULL, NULL, '2026-08-14 01:01:51', '2026-08-14 01:01:51', NULL),
	(5, 5, 8, 2, 1, NULL, 'Diploma in Psychology and Counseling', 'HS-DIP-PSY-001', 'diploma-in-psychology-and-counseling', 18, 'month', 0.00, NULL, 'LKR', 'A foundational diploma in psychological principles and counseling practice for aspiring counselors and support professionals.', '<p>A foundational diploma in psychological principles and counseling practice for aspiring counselors and support professionals.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:51', 1, 0, NULL, NULL, '2026-08-14 01:01:51', '2026-08-14 01:01:51', NULL),
	(6, 6, 9, 2, 1, NULL, 'Diploma in Early Childhood Care and Primary Education', 'EDU-DIP-ECE-001', 'diploma-in-early-childhood-care-and-primary-education', 18, 'month', 0.00, NULL, 'LKR', 'Prepares students for careers in early childhood development and primary-level teaching.', '<p>Prepares students for careers in early childhood development and primary-level teaching.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:51', 1, 0, NULL, NULL, '2026-08-14 01:01:51', '2026-08-14 01:01:51', NULL),
	(7, 2, 7, 1, 1, NULL, 'Certificate in Mechanical and Electrical Plumbing (MEP)', 'ENG-CERT-MEP-001', 'certificate-in-mechanical-and-electrical-plumbing-mep', 6, 'month', 0.00, NULL, 'LKR', 'Hands-on certificate covering mechanical, electrical, and plumbing systems used in building services.', '<p>Hands-on certificate covering mechanical, electrical, and plumbing systems used in building services.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:52', 1, 0, NULL, NULL, '2026-08-14 01:01:52', '2026-08-14 01:01:52', NULL),
	(8, 3, 5, 1, 1, NULL, 'Certificate in Information and Communication Technology', 'CS-CERT-ICT-001', 'certificate-in-information-and-communication-technology', 6, 'month', 0.00, NULL, 'LKR', 'An introductory ICT certificate covering computer literacy, office applications, and basic networking.', '<p>An introductory ICT certificate covering computer literacy, office applications, and basic networking.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:52', 1, 0, NULL, NULL, '2026-08-14 01:01:52', '2026-08-14 01:01:52', NULL),
	(9, 7, 10, 1, 1, NULL, 'Certificate in Second Language', 'ARTS-CERT-LANG-001', 'certificate-in-second-language', 6, 'month', 0.00, NULL, 'LKR', 'A practical language certificate building conversational and written fluency in a second language.', '<p>A practical language certificate building conversational and written fluency in a second language.</p>', NULL, NULL, NULL, 'published', '2026-08-14 06:31:54', 1, 0, NULL, NULL, '2026-08-14 01:01:54', '2026-08-14 01:01:54', NULL);

-- Dumping structure for table pn_knowledge_campus.course_categories
CREATE TABLE IF NOT EXISTS `course_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_categories_name_unique` (`name`),
  UNIQUE KEY `course_categories_slug_unique` (`slug`),
  KEY `course_categories_parent_id_order_index` (`parent_id`,`order`),
  CONSTRAINT `course_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.course_categories: ~6 rows (approximately)
INSERT INTO `course_categories` (`id`, `parent_id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'Business & Management', 'business-management', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 1, 'Accounting & Finance', 'accounting-finance', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 1, 'Marketing & HR', 'marketing-hr', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, NULL, 'Engineering & Technology', 'engineering-technology', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(5, NULL, 'Computing & IT', 'computing-it', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(6, 5, 'Software Development', 'software-development', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.course_curriculum_items
CREATE TABLE IF NOT EXISTS `course_curriculum_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_curriculum_items_course_id_index` (`course_id`),
  KEY `course_curriculum_items_parent_id_index` (`parent_id`),
  CONSTRAINT `course_curriculum_items_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_curriculum_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_curriculum_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.course_curriculum_items: ~9 rows (approximately)
INSERT INTO `course_curriculum_items` (`id`, `course_id`, `parent_id`, `title`, `description`, `duration`, `order`, `created_at`, `updated_at`) VALUES
	(1, 1, NULL, 'Year 1: Foundations', NULL, NULL, 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 1, 1, 'Programming Fundamentals', NULL, '12 weeks', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 1, 1, 'Mathematics for Computing', NULL, '12 weeks', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 1, NULL, 'Year 2: Core Systems', NULL, NULL, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(5, 1, 4, 'Data Structures & Algorithms', NULL, '12 weeks', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(6, 1, 4, 'Databases', NULL, '12 weeks', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(7, 2, NULL, 'Year 1: Foundations', NULL, NULL, 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(8, 2, 7, 'Programming Fundamentals', NULL, '12 weeks', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(9, 2, 7, 'Statistics I', NULL, '12 weeks', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.course_levels
CREATE TABLE IF NOT EXISTS `course_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_levels_name_unique` (`name`),
  UNIQUE KEY `course_levels_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.course_levels: ~4 rows (approximately)
INSERT INTO `course_levels` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Certificate', 'certificate', 0, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(2, 'Diploma', 'diploma', 1, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(3, 'Undergraduate', 'undergraduate', 2, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(4, 'Postgraduate', 'postgraduate', 3, '2026-08-10 04:23:10', '2026-08-10 04:23:10');

-- Dumping structure for table pn_knowledge_campus.course_modes
CREATE TABLE IF NOT EXISTS `course_modes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_modes_name_unique` (`name`),
  UNIQUE KEY `course_modes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.course_modes: ~4 rows (approximately)
INSERT INTO `course_modes` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Full-Time', 'full-time', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Part-Time', 'part-time', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Online', 'online', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 'Blended', 'blended', 3, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `faculty_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `order` smallint NOT NULL DEFAULT '0',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_slug_unique` (`slug`),
  KEY `departments_created_by_foreign` (`created_by`),
  KEY `departments_updated_by_foreign` (`updated_by`),
  KEY `departments_faculty_id_index` (`faculty_id`),
  KEY `departments_status_index` (`status`),
  KEY `departments_order_index` (`order`),
  CONSTRAINT `departments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `departments_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `departments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.departments: ~11 rows (approximately)
INSERT INTO `departments` (`id`, `faculty_id`, `name`, `slug`, `short_description`, `description`, `order`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'Department of Accounting & Finance', 'department-of-accounting-finance', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:34:56', '2026-08-17 01:34:56'),
	(2, 1, 'Department of Marketing', 'department-of-marketing', NULL, NULL, 1, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:35:02', '2026-08-17 01:35:02'),
	(3, 2, 'Department of Civil Engineering', 'department-of-civil-engineering', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(4, 2, 'Department of Electrical Engineering', 'department-of-electrical-engineering', NULL, NULL, 1, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:34:04', '2026-08-17 01:34:04'),
	(5, 3, 'Department of Computer Science', 'department-of-computer-science', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(6, 3, 'Department of Software Engineering', 'department-of-software-engineering', NULL, NULL, 1, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:35:59', '2026-08-17 01:35:59'),
	(7, 2, 'Department of Mechanical Engineering', 'department-of-mechanical-engineering', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-14 01:00:39', '2026-08-14 01:00:39', NULL),
	(8, 5, 'Department of Psychology & Counseling', 'department-of-psychology-counseling', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-14 01:00:39', '2026-08-14 01:00:39', NULL),
	(9, 6, 'Department of Early Childhood Education', 'department-of-early-childhood-education', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-14 01:00:39', '2026-08-14 01:00:39', NULL),
	(10, 7, 'Department of Languages', 'department-of-languages', NULL, NULL, 0, 'published', NULL, NULL, '2026-08-14 01:00:55', '2026-08-14 01:00:55', NULL),
	(11, 5, 'New Department', 'new-department', NULL, NULL, 0, 'draft', 1, NULL, '2026-08-17 01:37:01', '2026-08-17 01:37:01', NULL);

-- Dumping structure for table pn_knowledge_campus.downloads
CREATE TABLE IF NOT EXISTS `downloads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `requires_inquiry` tinyint(1) NOT NULL DEFAULT '0',
  `download_count` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `downloads_category_id_foreign` (`category_id`),
  KEY `downloads_created_by_foreign` (`created_by`),
  KEY `downloads_updated_by_foreign` (`updated_by`),
  KEY `downloads_is_active_order_index` (`is_active`,`order`),
  CONSTRAINT `downloads_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `download_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `downloads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `downloads_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.downloads: ~4 rows (approximately)
INSERT INTO `downloads` (`id`, `category_id`, `title`, `description`, `order`, `is_active`, `requires_inquiry`, `download_count`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Undergraduate Prospectus 2026', 'A complete guide to our undergraduate programmes, faculties, and campus life.', 0, 1, 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 2, 'Application Form', 'The standard application form for all undergraduate and postgraduate programmes.', 0, 1, 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 2, 'Scholarship Application Form', 'Required for students applying for merit-based or need-based scholarships.', 1, 1, 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 3, 'Faculty of Computing Brochure', 'An overview of courses, facilities, and career outcomes in the Faculty of Computing.', 0, 1, 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.download_attachments
CREATE TABLE IF NOT EXISTS `download_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `download_id` bigint unsigned NOT NULL,
  `attachable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `download_attachments_unique` (`download_id`,`attachable_type`,`attachable_id`),
  KEY `download_attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  CONSTRAINT `download_attachments_download_id_foreign` FOREIGN KEY (`download_id`) REFERENCES `downloads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.download_attachments: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.download_categories
CREATE TABLE IF NOT EXISTS `download_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `download_categories_name_unique` (`name`),
  UNIQUE KEY `download_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.download_categories: ~4 rows (approximately)
INSERT INTO `download_categories` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Prospectus', 'prospectus', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Forms', 'forms', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Brochures', 'brochures', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 'PDFs', 'pdfs', 3, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.events
CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','scheduled','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`),
  KEY `events_created_by_foreign` (`created_by`),
  KEY `events_updated_by_foreign` (`updated_by`),
  KEY `events_starts_at_index` (`starts_at`),
  KEY `events_status_index` (`status`),
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.events: ~3 rows (approximately)
INSERT INTO `events` (`id`, `title`, `slug`, `venue`, `is_online`, `starts_at`, `ends_at`, `description`, `registration_url`, `status`, `published_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Open Day 2026', 'open-day-2026', 'Main Campus Auditorium', 0, '2026-08-24 09:53:11', '2026-08-24 13:53:11', 'Details for Open Day 2026.', NULL, 'published', NULL, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(2, 'Annual Tech Symposium', 'annual-tech-symposium', NULL, 1, '2026-09-10 09:53:11', '2026-09-10 15:53:11', 'Details for Annual Tech Symposium.', NULL, 'published', NULL, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(3, 'Graduation Ceremony 2025', 'graduation-ceremony-2025', 'Grand Hall', 0, '2026-06-10 09:53:11', '2026-06-10 12:53:11', 'Details for Graduation Ceremony 2025.', NULL, 'published', NULL, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL);

-- Dumping structure for table pn_knowledge_campus.event_speakers
CREATE TABLE IF NOT EXISTS `event_speakers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_speakers_event_id_order_index` (`event_id`,`order`),
  CONSTRAINT `event_speakers_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.event_speakers: ~0 rows (approximately)
INSERT INTO `event_speakers` (`id`, `event_id`, `name`, `title`, `bio`, `order`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Dr. Amara Silva', 'Dean of Admissions', NULL, 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.faculties
CREATE TABLE IF NOT EXISTS `faculties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `dean_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dean_title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dean_message` text COLLATE utf8mb4_unicode_ci,
  `order` smallint NOT NULL DEFAULT '0',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculties_slug_unique` (`slug`),
  KEY `faculties_created_by_foreign` (`created_by`),
  KEY `faculties_updated_by_foreign` (`updated_by`),
  KEY `faculties_status_index` (`status`),
  KEY `faculties_order_index` (`order`),
  CONSTRAINT `faculties_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `faculties_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.faculties: ~7 rows (approximately)
INSERT INTO `faculties` (`id`, `name`, `slug`, `short_description`, `description`, `dean_name`, `dean_title`, `dean_message`, `order`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Faculty of Business', 'faculty-of-business', 'Building the next generation of business leaders and entrepreneurs.', 'The Faculty of Business offers a range of undergraduate and postgraduate programmes spanning management, finance, marketing, and entrepreneurship.', 'Dr. Amara Chukwu', 'Dean, Faculty of Business', 'Our faculty is committed to producing graduates who are ready to lead in a fast-changing global economy.', 0, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:35:07', '2026-08-17 01:35:07'),
	(2, 'Faculty of Engineering', 'faculty-of-engineering', 'Practical, hands-on engineering education across multiple disciplines.', 'The Faculty of Engineering delivers rigorous programmes in civil, mechanical, electrical, and computer engineering, backed by modern labs and industry partnerships.', 'Prof. Samuel Owusu', 'Dean, Faculty of Engineering', 'We prepare engineers who can solve real problems from day one.', 1, 'published', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(3, 'Faculty of Computing', 'faculty-of-computing', 'Building the next generation of tech talent through hands-on learning and industry-relevant skills.', 'The Faculty of Computing prepares students for careers at the forefront of technology, with programmes spanning software engineering, computer science fundamentals, and data science. The curriculum balances strong theoretical grounding in algorithms, systems, and data with extensive hands-on coding, project work, and exposure to current industry tools and practices. Graduates leave ready to build, secure, and scale the software and data systems driving today\'s digital economy.', 'Dr. Grace Mensah', 'Dean, Faculty of Computing', 'Technology moves fast, and so do we — our curriculum evolves every year to keep pace with industry.', 2, 'published', NULL, 1, '2026-08-10 04:23:10', '2026-08-17 02:08:03', NULL),
	(4, 'Faculty of Arts', 'new-faculty', NULL, NULL, NULL, NULL, NULL, 3, 'draft', 1, 1, '2026-08-14 00:57:07', '2026-08-14 00:57:48', '2026-08-14 00:57:48'),
	(5, 'Faculty of Health Sciences', 'faculty-of-health-sciences', 'Preparing skilled, compassionate healthcare professionals through evidence-based education and clinical training.', 'The Faculty of Health Sciences equips students with the knowledge, clinical skills, and ethical grounding needed for careers across the healthcare sector. Programmes combine rigorous theoretical foundations with hands-on clinical placements, simulation labs, and community health experience, preparing graduates to deliver safe, patient-centred care from day one. Students are mentored by experienced practitioners and engage with real-world case studies, public health challenges, and the latest advances in medical practice.', NULL, NULL, NULL, 0, 'published', NULL, 1, '2026-08-14 01:00:39', '2026-08-17 01:57:20', NULL),
	(6, 'Faculty of Education', 'faculty-of-education', 'Shaping the next generation of educators, trainers, and academic leaders.', 'The Faculty of Education trains future teachers, trainers, and education administrators to design meaningful learning experiences across all levels of schooling. The curriculum blends pedagogy, educational psychology, curriculum design, and classroom technology with supervised teaching practice in real school settings. Graduates leave equipped not only to teach, but to lead, innovate, and advocate for inclusive, effective education systems.', NULL, NULL, NULL, 0, 'published', NULL, 1, '2026-08-14 01:00:39', '2026-08-17 02:03:00', NULL),
	(7, 'Faculty of Arts', 'faculty-of-arts', 'Cultivating creativity, critical thinking, and cultural understanding across the humanities and creative disciplines.', 'The Faculty of Arts offers a broad, interdisciplinary education spanning humanities, languages, media, and creative practice. Students develop strong analytical and communication skills while exploring literature, history, culture, and the arts, guided by faculty who encourage independent thought and creative expression. Graduates go on to careers in media, publishing, communications, cultural institutions, and beyond - equipped with the versatile, transferable skills employers value most.', NULL, NULL, NULL, 0, 'published', NULL, 1, '2026-08-14 01:00:39', '2026-08-17 02:08:55', NULL);

-- Dumping structure for table pn_knowledge_campus.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.faqs
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `faqable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faqable_id` bigint unsigned DEFAULT NULL,
  `question` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_faqable_type_faqable_id_index` (`faqable_type`,`faqable_id`),
  KEY `faqs_is_active_order_index` (`is_active`,`order`),
  KEY `faqs_category_id_foreign` (`category_id`),
  CONSTRAINT `faqs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `faq_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.faqs: ~7 rows (approximately)
INSERT INTO `faqs` (`id`, `category_id`, `faqable_type`, `faqable_id`, `question`, `answer`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'course', 1, 'What are the entry requirements?', '3 passes at GCE A/L including Mathematics.', 0, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, NULL, 'course', 1, 'Is there a placement year?', 'Yes, an optional industry placement is available after Year 2.', 1, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, NULL, 'course', 2, 'Do I need a programming background?', 'No prior experience is required — the first year covers the fundamentals.', 0, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 1, NULL, NULL, 'How do I apply for admission?', 'Visit the How to Apply page and submit the online application form along with the required documents.', 0, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(5, 1, NULL, NULL, 'What documents are required for admission?', 'You will need your academic transcripts, a copy of your national ID or passport, and passport-sized photographs.', 1, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(6, 2, NULL, NULL, 'Are scholarships available?', 'Yes — merit-based and need-based scholarships are available. See the Scholarships page for eligibility criteria.', 0, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(7, 3, NULL, NULL, 'What facilities are available on campus?', 'The campus offers a library, computer labs, sports facilities, and student clubs covering a wide range of interests.', 0, 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.faq_categories
CREATE TABLE IF NOT EXISTS `faq_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faq_categories_name_unique` (`name`),
  UNIQUE KEY `faq_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.faq_categories: ~3 rows (approximately)
INSERT INTO `faq_categories` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Admissions', 'admissions', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Fees & Scholarships', 'fees-scholarships', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Campus Life', 'campus-life', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.gallery_albums
CREATE TABLE IF NOT EXISTS `gallery_albums` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gallery_albums_slug_unique` (`slug`),
  KEY `gallery_albums_created_by_foreign` (`created_by`),
  KEY `gallery_albums_updated_by_foreign` (`updated_by`),
  KEY `gallery_albums_is_active_order_index` (`is_active`,`order`),
  CONSTRAINT `gallery_albums_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gallery_albums_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.gallery_albums: ~3 rows (approximately)
INSERT INTO `gallery_albums` (`id`, `title`, `slug`, `description`, `order`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Open Day 2026', 'open-day-2026', NULL, 0, 1, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(2, 'Campus Life', 'campus-life', NULL, 1, 1, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(3, 'Graduation Ceremony 2025', 'graduation-ceremony-2025', NULL, 2, 1, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL);

-- Dumping structure for table pn_knowledge_campus.hero_slides
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_text` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hero_slides_created_by_foreign` (`created_by`),
  KEY `hero_slides_updated_by_foreign` (`updated_by`),
  KEY `hero_slides_is_active_order_index` (`is_active`,`order`),
  KEY `hero_slides_starts_at_ends_at_index` (`starts_at`,`ends_at`),
  CONSTRAINT `hero_slides_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hero_slides_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.hero_slides: ~3 rows (approximately)
INSERT INTO `hero_slides` (`id`, `title`, `subtitle`, `cta_text`, `cta_url`, `order`, `starts_at`, `ends_at`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Welcome to PNK Global Campus', 'Building futures through knowledge, character, and community.', NULL, NULL, 0, NULL, NULL, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-14 01:56:24', NULL),
	(2, 'Admissions Now Open', 'Start your journey with us today.', NULL, NULL, 1, NULL, NULL, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-14 01:56:24', NULL),
	(3, 'A Campus Built for Student Life', 'Clubs, sports, and events beyond the classroom.', NULL, NULL, 2, NULL, NULL, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-14 01:56:24', NULL);

-- Dumping structure for table pn_knowledge_campus.homepage_sections
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `homepage_sections_section_key_unique` (`section_key`),
  KEY `homepage_sections_created_by_foreign` (`created_by`),
  KEY `homepage_sections_updated_by_foreign` (`updated_by`),
  KEY `homepage_sections_order_index` (`order`),
  CONSTRAINT `homepage_sections_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `homepage_sections_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.homepage_sections: ~12 rows (approximately)
INSERT INTO `homepage_sections` (`id`, `section_key`, `order`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 'hero', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(2, 'welcome', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(3, 'featured_courses', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(4, 'faculties', 3, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(5, 'why_choose_us', 4, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(6, 'statistics', 5, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(7, 'testimonials', 6, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(8, 'partners', 7, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(9, 'latest_news', 8, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(10, 'upcoming_events', 9, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(11, 'cta', 10, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13'),
	(12, 'footer_widgets', 11, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 02:23:13');

-- Dumping structure for table pn_knowledge_campus.inquiries
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_page` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `international_applicant` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('new','in_progress','resolved','spam') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inquiries_course_id_foreign` (`course_id`),
  KEY `inquiries_status_index` (`status`),
  KEY `inquiries_created_at_index` (`created_at`),
  KEY `inquiries_assigned_to_index` (`assigned_to`),
  CONSTRAINT `inquiries_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inquiries_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.inquiries: ~1 rows (approximately)
INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `message`, `source_page`, `course_id`, `international_applicant`, `status`, `assigned_to`, `created_at`, `updated_at`) VALUES
	(1, 'Test User', 'testuser@gmail.com', NULL, 'test', '/contact', NULL, 0, 'in_progress', 1, '2026-08-17 03:55:56', '2026-08-17 03:56:50');

-- Dumping structure for table pn_knowledge_campus.inquiry_notes
CREATE TABLE IF NOT EXISTS `inquiry_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inquiry_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inquiry_notes_user_id_foreign` (`user_id`),
  KEY `inquiry_notes_inquiry_id_index` (`inquiry_id`),
  CONSTRAINT `inquiry_notes_inquiry_id_foreign` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inquiry_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.inquiry_notes: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.jobs: ~6 rows (approximately)
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
	(1, 'default', '{"uuid":"c2b6d69c-381a-4c2a-95dd-20bc577faaf0","displayName":"App\\\\Notifications\\\\NewApplicationNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:15:\\"App\\\\Models\\\\User\\";s:2:\\"id\\";a:1:{i:0;i:1;}s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:12:\\"notification\\";O:44:\\"App\\\\Notifications\\\\NewApplicationNotification\\":2:{s:11:\\"application\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:22:\\"App\\\\Models\\\\Application\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:1:{i:0;s:5:\\"media\\";}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"a3e3a6cb-265a-4a2b-a7d5-d430ead5a7e1\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1786957032,"delay":null}', 0, NULL, 1786957033, 1786957033),
	(2, 'default', '{"uuid":"7ebafc9a-c066-4ee9-9aee-5168ca433154","displayName":"App\\\\Notifications\\\\NewApplicationNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:15:\\"App\\\\Models\\\\User\\";s:2:\\"id\\";a:1:{i:0;i:1;}s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:12:\\"notification\\";O:44:\\"App\\\\Notifications\\\\NewApplicationNotification\\":2:{s:11:\\"application\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:22:\\"App\\\\Models\\\\Application\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:1:{i:0;s:5:\\"media\\";}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"a3e3a6cb-265a-4a2b-a7d5-d430ead5a7e1\\";}s:8:\\"channels\\";a:1:{i:0;s:8:\\"database\\";}}","batchId":null},"createdAt":1786957033,"delay":null}', 0, NULL, 1786957033, 1786957033),
	(3, 'default', '{"uuid":"ee5360dc-4148-469c-8f17-b3702f0e02e3","displayName":"App\\\\Notifications\\\\ApplicationReceivedNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:29:\\"Illuminate\\\\Support\\\\Collection\\":2:{s:8:\\"\\u0000*\\u0000items\\";a:1:{i:0;O:44:\\"Illuminate\\\\Notifications\\\\AnonymousNotifiable\\":1:{s:6:\\"routes\\";a:1:{s:4:\\"mail\\";s:14:\\"test@gmail.com\\";}}}s:28:\\"\\u0000*\\u0000escapeWhenCastingToString\\";b:0;}s:12:\\"notification\\";O:49:\\"App\\\\Notifications\\\\ApplicationReceivedNotification\\":2:{s:11:\\"application\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:22:\\"App\\\\Models\\\\Application\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:1:{i:0;s:5:\\"media\\";}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"ba7bd319-e12b-4842-bd82-bee7f25ef39b\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1786957033,"delay":null}', 0, NULL, 1786957033, 1786957033),
	(4, 'default', '{"uuid":"cc6e6a3f-cdfd-47f9-a9d9-1338a4013794","displayName":"App\\\\Notifications\\\\ApplicationStatusNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:29:\\"Illuminate\\\\Support\\\\Collection\\":2:{s:8:\\"\\u0000*\\u0000items\\";a:1:{i:0;O:44:\\"Illuminate\\\\Notifications\\\\AnonymousNotifiable\\":1:{s:6:\\"routes\\";a:1:{s:4:\\"mail\\";s:14:\\"test@gmail.com\\";}}}s:28:\\"\\u0000*\\u0000escapeWhenCastingToString\\";b:0;}s:12:\\"notification\\";O:47:\\"App\\\\Notifications\\\\ApplicationStatusNotification\\":2:{s:11:\\"application\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:22:\\"App\\\\Models\\\\Application\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"1034069e-6419-4804-9127-366d96489967\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1786957091,"delay":null}', 0, NULL, 1786957091, 1786957091),
	(5, 'default', '{"uuid":"3b6dee39-10bb-4caf-be65-c54d88ac1802","displayName":"App\\\\Notifications\\\\NewInquiryNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:29:\\"Illuminate\\\\Support\\\\Collection\\":2:{s:8:\\"\\u0000*\\u0000items\\";a:1:{i:0;O:44:\\"Illuminate\\\\Notifications\\\\AnonymousNotifiable\\":1:{s:6:\\"routes\\";a:1:{s:4:\\"mail\\";s:19:\\"info@pnkgcampus.com\\";}}}s:28:\\"\\u0000*\\u0000escapeWhenCastingToString\\";b:0;}s:12:\\"notification\\";O:40:\\"App\\\\Notifications\\\\NewInquiryNotification\\":2:{s:7:\\"inquiry\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:18:\\"App\\\\Models\\\\Inquiry\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"d05cf9a9-5a21-4ede-ad9d-43ff2b3ceb2e\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1786958756,"delay":null}', 0, NULL, 1786958756, 1786958756),
	(6, 'default', '{"uuid":"1c631103-f77f-4b4e-96d6-c9e9cba3c0b4","displayName":"App\\\\Notifications\\\\InquiryConfirmationNotification","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"Illuminate\\\\Notifications\\\\SendQueuedNotifications","command":"O:48:\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\":3:{s:11:\\"notifiables\\";O:29:\\"Illuminate\\\\Support\\\\Collection\\":2:{s:8:\\"\\u0000*\\u0000items\\";a:1:{i:0;O:44:\\"Illuminate\\\\Notifications\\\\AnonymousNotifiable\\":1:{s:6:\\"routes\\";a:1:{s:4:\\"mail\\";s:18:\\"testuser@gmail.com\\";}}}s:28:\\"\\u0000*\\u0000escapeWhenCastingToString\\";b:0;}s:12:\\"notification\\";O:49:\\"App\\\\Notifications\\\\InquiryConfirmationNotification\\":2:{s:7:\\"inquiry\\";O:45:\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\":5:{s:5:\\"class\\";s:18:\\"App\\\\Models\\\\Inquiry\\";s:2:\\"id\\";i:1;s:9:\\"relations\\";a:0:{}s:10:\\"connection\\";s:5:\\"mysql\\";s:15:\\"collectionClass\\";N;}s:2:\\"id\\";s:36:\\"bce44abb-dbc0-4a2c-98bc-4b790fd88c67\\";}s:8:\\"channels\\";a:1:{i:0;s:4:\\"mail\\";}}","batchId":null},"createdAt":1786958756,"delay":null}', 0, NULL, 1786958756, 1786958756);

-- Dumping structure for table pn_knowledge_campus.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.job_batches: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.media
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `folder_id` bigint unsigned DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_folder_id_foreign` (`folder_id`),
  KEY `media_uploaded_by_foreign` (`uploaded_by`),
  KEY `media_collection_name_index` (`collection_name`),
  KEY `media_order_column_index` (`order_column`),
  CONSTRAINT `media_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `media_folders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.media: ~13 rows (approximately)
INSERT INTO `media` (`id`, `model_type`, `model_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `folder_id`, `alt_text`, `uploaded_by`, `created_at`, `updated_at`) VALUES
	(1, 'media_library', 1, 'c81289b1-3cf7-4463-8ceb-b4892f70fd75', 'library', 'pnkg logo', 'pnkg-logo.jpeg', 'image/jpeg', 'public', 'public', 146554, '[]', '{"width": 1536, "height": 1024}', '{"web": true, "thumb": true}', '[]', 1, NULL, 'PNK logo', 1, '2026-08-14 00:43:07', '2026-08-14 00:43:13'),
	(2, 'media_library', 1, '4a43912a-3c19-49af-8b15-4ad0bf4dbc4a', 'library', 'pnkg logo', 'pnkg-logo.jpeg', 'image/jpeg', 'public', 'public', 146554, '[]', '{"width": 1536, "height": 1024}', '{"web": true, "thumb": true}', '[]', 2, NULL, 'PNK Logo', 1, '2026-08-14 00:44:30', '2026-08-14 00:44:32'),
	(3, 'media_library', 1, '0a8c63f9-5287-4a6d-82c7-e02af7dcb757', 'library', 'pnkg logo', 'new-logo.jpeg', 'image/jpeg', 'public', 'public', 146554, '[]', '[]', '{"web": true, "thumb": true}', '[]', 3, NULL, NULL, NULL, '2026-08-14 01:31:54', '2026-08-14 01:31:55'),
	(4, 'hero_slide', 1, '19b6ef45-3773-40a2-ba32-8446848068d5', 'slide_image', 'hero slide 1', 'hero1.jpg', 'image/jpeg', 'public', 'public', 38338, '[]', '[]', '{"web": true, "thumb": true}', '[]', 1, NULL, NULL, NULL, '2026-08-14 01:48:54', '2026-08-14 01:48:54'),
	(5, 'hero_slide', 2, 'ea897616-fb86-4439-9d12-cb9b49ffd169', 'slide_image', 'hero slide 2', 'hero2.jpg', 'image/jpeg', 'public', 'public', 608416, '[]', '[]', '{"web": true, "thumb": true}', '[]', 1, NULL, NULL, NULL, '2026-08-14 01:51:12', '2026-08-14 01:51:13'),
	(6, 'hero_slide', 3, '25a58505-6389-486f-aae7-a34c6b60b223', 'slide_image', 'hero slide 3', 'hero3.jpg', 'image/jpeg', 'public', 'public', 88515, '[]', '[]', '{"web": true, "thumb": true}', '[]', 1, NULL, NULL, NULL, '2026-08-14 01:51:17', '2026-08-14 01:51:18'),
	(7, 'media_library', 1, '076f589b-c7ef-4e01-93ef-a72de5dbed35', 'library', 'chairman photo', 'chairman.jpg', 'image/jpeg', 'public', 'public', 131889, '[]', '[]', '{"web": true, "thumb": true}', '[]', 4, NULL, NULL, NULL, '2026-08-14 03:15:39', '2026-08-14 03:15:40'),
	(8, 'media_library', 1, '093c6018-69be-4265-a444-083a46df3762', 'library', 'manager', 'manager.jpeg', 'image/jpeg', 'public', 'public', 200300, '[]', '{"width": 1421, "height": 1600}', '{"web": true, "thumb": true}', '[]', 5, NULL, 'manager', 1, '2026-08-17 01:09:20', '2026-08-17 01:09:21'),
	(10, 'faculty', 5, 'dcd5f870-c2b3-48a8-9fe9-8a7a7bcff832', 'banner', 'faculty of health science', 'faculty-of-health-science.jpg', 'image/jpeg', 'public', 'public', 216711, '[]', '{"width": 1200, "height": 630}', '{"web": true, "thumb": true}', '[]', 6, NULL, 'faculty of health science', 1, '2026-08-17 01:53:57', '2026-08-17 01:53:58'),
	(14, 'faculty', 6, '91a80427-1d4b-4f7e-ae41-50e76a869a7d', 'banner', 'faculty of education', 'faculty-of-education.jpg', 'image/jpeg', 'public', 'public', 237272, '[]', '{"width": 2000, "height": 1334}', '{"web": true, "thumb": true}', '[]', 8, NULL, 'faculty of education', 1, '2026-08-17 02:02:33', '2026-08-17 02:02:34'),
	(15, 'faculty', 7, '0610f217-7eea-4448-835a-ff2d4cc06ca0', 'banner', 'faculty of arts', 'faculty-of-arts.jpg', 'image/jpeg', 'public', 'public', 237272, '[]', '{"width": 2000, "height": 1334}', '{"web": true, "thumb": true}', '[]', 6, NULL, 'faculty of arts', 1, '2026-08-17 02:04:12', '2026-08-17 02:04:13'),
	(16, 'faculty', 2, '30fa96f6-0d6a-4c96-a093-05496cc857e7', 'banner', 'faculty of engineering', 'faculty-of-engineering.jpg', 'image/jpeg', 'public', 'public', 395808, '[]', '{"width": 2085, "height": 1390}', '{"web": true, "thumb": true}', '[]', 7, NULL, 'faculty of engineering', 1, '2026-08-17 02:05:03', '2026-08-17 02:05:04'),
	(18, 'faculty', 3, 'd4292267-adbf-4f96-8858-f8942f3f4485', 'banner', 'faculty of computing', 'faculty-of-computing.jpg', 'image/jpeg', 'public', 'public', 76960, '[]', '{"width": 1033, "height": 768}', '{"web": true, "thumb": true}', '[]', 6, NULL, 'faculty of computing', 1, '2026-08-17 02:07:27', '2026-08-17 02:07:28'),
	(19, 'application', 1, 'bb936ea2-fd81-4559-b14f-a7df7e9632ee', 'documents', 'faculty of education', 'faculty-of-education.jpg', 'image/jpeg', 'local', 'local', 237272, '[]', '{"label": "image"}', '[]', '[]', 1, NULL, NULL, NULL, '2026-08-17 03:27:01', '2026-08-17 03:27:01');

-- Dumping structure for table pn_knowledge_campus.media_folders
CREATE TABLE IF NOT EXISTS `media_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_folders_created_by_foreign` (`created_by`),
  KEY `media_folders_parent_id_index` (`parent_id`),
  CONSTRAINT `media_folders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `media_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.media_folders: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.media_library
CREATE TABLE IF NOT EXISTS `media_library` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.media_library: ~0 rows (approximately)
INSERT INTO `media_library` (`id`, `created_at`, `updated_at`) VALUES
	(1, '2026-08-10 04:23:01', '2026-08-10 04:23:01');

-- Dumping structure for table pn_knowledge_campus.menus
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_name_unique` (`name`),
  KEY `menus_created_by_foreign` (`created_by`),
  KEY `menus_updated_by_foreign` (`updated_by`),
  CONSTRAINT `menus_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menus_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.menus: ~2 rows (approximately)
INSERT INTO `menus` (`id`, `name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'header', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(2, 'footer', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL);

-- Dumping structure for table pn_knowledge_campus.menu_items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkable_id` bigint unsigned DEFAULT NULL,
  `custom_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_mega_menu` tinyint(1) NOT NULL DEFAULT '0',
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `visible_on` enum('both','desktop','mobile') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_created_by_foreign` (`created_by`),
  KEY `menu_items_updated_by_foreign` (`updated_by`),
  KEY `menu_items_menu_id_parent_id_order_index` (`menu_id`,`parent_id`,`order`),
  KEY `menu_items_linkable_type_linkable_id_index` (`linkable_type`,`linkable_id`),
  KEY `menu_items_starts_at_ends_at_index` (`starts_at`,`ends_at`),
  CONSTRAINT `menu_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.menu_items: ~29 rows (approximately)
INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `linkable_type`, `linkable_id`, `custom_url`, `description`, `icon`, `is_mega_menu`, `open_in_new_tab`, `order`, `is_active`, `starts_at`, `ends_at`, `visible_on`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 1, NULL, 'Home', NULL, NULL, '/', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(2, 1, NULL, 'About', NULL, NULL, '/about', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(7, 1, NULL, 'Academics', NULL, NULL, '/courses', NULL, NULL, 1, 0, 2, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(8, 1, 7, 'Faculties', NULL, NULL, '/faculties', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(9, 1, 7, 'Departments', NULL, NULL, '/departments', NULL, NULL, 0, 0, 1, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(10, 1, 7, 'Courses', NULL, NULL, '/courses', NULL, NULL, 0, 0, 2, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(17, 1, NULL, 'News & Media', NULL, NULL, '/news', NULL, NULL, 0, 0, 4, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(18, 1, 17, 'News', NULL, NULL, '/news', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(19, 1, 17, 'Blog', NULL, NULL, '/blog', NULL, NULL, 0, 0, 1, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(20, 1, 17, 'Events', NULL, NULL, '/events', NULL, NULL, 0, 0, 2, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(21, 1, 17, 'Gallery', NULL, NULL, '/gallery', NULL, NULL, 0, 0, 3, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(22, 1, NULL, 'Career', NULL, NULL, '/career', NULL, NULL, 0, 0, 5, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(24, 1, NULL, 'Contact', NULL, NULL, '/contact', NULL, NULL, 0, 0, 7, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:07:30'),
	(25, 2, NULL, 'About', NULL, NULL, '/about', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(26, 2, NULL, 'Courses', NULL, NULL, '/courses', NULL, NULL, 0, 0, 1, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(27, 2, NULL, 'Admissions', NULL, NULL, '/admissions', NULL, NULL, 0, 0, 2, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(28, 2, NULL, 'News', NULL, NULL, '/news', NULL, NULL, 0, 0, 3, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(29, 2, NULL, 'FAQ', NULL, NULL, '/faq', NULL, NULL, 0, 0, 4, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(30, 2, NULL, 'Downloads', NULL, NULL, '/downloads', NULL, NULL, 0, 0, 5, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(31, 2, NULL, 'Apply Now', NULL, NULL, '/apply', NULL, NULL, 0, 0, 6, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(32, 2, NULL, 'Contact', NULL, NULL, '/contact', NULL, NULL, 0, 0, 7, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(33, 2, NULL, 'Privacy Policy', NULL, NULL, '/privacy-policy', NULL, NULL, 0, 0, 8, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(34, 2, NULL, 'Terms & Conditions', NULL, NULL, '/terms', NULL, NULL, 0, 0, 9, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(35, 2, NULL, 'Refund Policy', NULL, NULL, '/refund-policy', NULL, NULL, 0, 0, 10, 1, NULL, NULL, 'both', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(36, 1, NULL, 'Student Life', NULL, NULL, '#', NULL, NULL, 0, 0, 1, 1, NULL, NULL, 'both', 1, NULL, '2026-08-17 23:06:22', '2026-08-17 23:07:30'),
	(37, 1, 36, 'Registration', NULL, NULL, '/apply', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', 1, 1, '2026-08-17 23:07:56', '2026-08-17 23:14:50'),
	(38, 1, 36, 'Examination', NULL, NULL, '/student-life/examination', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', 1, NULL, '2026-08-17 23:08:23', '2026-08-17 23:08:23'),
	(39, 1, 36, 'Certificate Verification', NULL, NULL, '/student-life/certificate-verification', NULL, NULL, 0, 0, 0, 1, NULL, NULL, 'both', 1, NULL, '2026-08-17 23:08:52', '2026-08-17 23:08:52');

-- Dumping structure for table pn_knowledge_campus.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.migrations: ~0 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_01_01_000000_create_personal_access_tokens_table', 1),
	(5, '2026_01_01_000001_create_permission_tables', 1),
	(6, '2026_02_01_000000_create_settings_table', 1),
	(7, '2026_02_01_000001_create_branches_table', 1),
	(8, '2026_02_01_000002_create_social_links_table', 1),
	(9, '2026_02_01_000003_create_media_folders_table', 1),
	(10, '2026_02_01_000004_create_media_library_table', 1),
	(11, '2026_02_01_000005_create_media_table', 1),
	(12, '2026_02_01_000006_create_seo_meta_table', 1),
	(13, '2026_02_01_000007_create_url_redirects_table', 1),
	(14, '2026_02_02_000000_create_office_hours_table', 1),
	(15, '2026_02_03_000000_create_menus_table', 1),
	(16, '2026_02_03_000001_create_menu_items_table', 1),
	(17, '2026_02_04_000000_create_pages_table', 1),
	(18, '2026_02_04_000001_create_page_blocks_table', 1),
	(19, '2026_02_05_000000_create_homepage_sections_table', 1),
	(20, '2026_02_05_000001_create_hero_slides_table', 1),
	(21, '2026_02_05_000002_create_testimonials_table', 1),
	(22, '2026_02_05_000003_create_partners_table', 1),
	(23, '2026_02_06_000000_create_faculties_table', 1),
	(24, '2026_02_07_000000_create_departments_table', 1),
	(25, '2026_02_08_000000_create_course_levels_table', 1),
	(26, '2026_02_08_000001_create_course_modes_table', 1),
	(27, '2026_02_08_000002_create_course_categories_table', 1),
	(28, '2026_02_08_000003_create_courses_table', 1),
	(29, '2026_02_08_000004_create_course_curriculum_items_table', 1),
	(30, '2026_02_08_000005_create_faqs_table', 1),
	(31, '2026_02_08_000006_add_course_foreign_key_to_testimonials_table', 1),
	(32, '2026_02_09_000000_add_parent_to_course_categories_table', 1),
	(33, '2026_02_10_000000_create_blog_categories_table', 1),
	(34, '2026_02_10_000001_create_tags_table', 1),
	(35, '2026_02_10_000002_create_blog_posts_table', 1),
	(36, '2026_02_10_000003_create_taggables_table', 1),
	(37, '2026_02_11_000000_create_news_categories_table', 1),
	(38, '2026_02_11_000001_create_news_table', 1),
	(39, '2026_02_12_000000_create_events_table', 1),
	(40, '2026_02_12_000001_create_event_speakers_table', 1),
	(41, '2026_02_13_000000_create_gallery_albums_table', 1),
	(42, '2026_02_14_000000_create_inquiries_table', 1),
	(43, '2026_07_06_000001_create_partner_categories_table', 1),
	(44, '2026_07_06_000002_add_category_id_to_partners_table', 1),
	(45, '2026_07_06_062713_create_notifications_table', 1),
	(46, '2026_07_06_110341_add_published_at_to_events_table', 1),
	(47, '2026_07_06_144054_add_image_fields_to_seo_meta_table', 1),
	(48, '2026_07_06_151319_add_assigned_to_to_inquiries_table', 1),
	(49, '2026_07_06_151320_create_inquiry_notes_table', 1),
	(50, '2026_07_07_000001_create_faq_categories_table', 1),
	(51, '2026_07_07_000002_add_category_id_to_faqs_table', 1),
	(52, '2026_07_08_000001_create_download_categories_table', 1),
	(53, '2026_07_08_000002_create_downloads_table', 1),
	(54, '2026_07_09_000001_create_applications_table', 1),
	(55, '2026_07_10_000001_create_page_views_table', 1),
	(56, '2026_07_11_000001_add_gating_fields_to_downloads_table', 1),
	(57, '2026_07_11_000002_create_download_attachments_table', 1),
	(58, '2026_07_12_000001_add_soft_deletes_to_pages_table', 1),
	(59, '2026_07_13_000001_add_soft_deletes_to_hero_slides_table', 1),
	(60, '2026_07_13_000002_add_soft_deletes_to_testimonials_table', 1),
	(61, '2026_07_13_000003_add_soft_deletes_to_partners_table', 1),
	(62, '2026_07_13_000004_add_soft_deletes_to_menus_table', 1),
	(63, '2026_07_13_000005_add_soft_deletes_to_applications_table', 1);

-- Dumping structure for table pn_knowledge_campus.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.model_has_permissions: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.model_has_roles: ~0 rows (approximately)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'user', 1);

-- Dumping structure for table pn_knowledge_campus.news
CREATE TABLE IF NOT EXISTS `news` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `status` enum('draft','published','scheduled','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `views_count` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_slug_unique` (`slug`),
  KEY `news_author_id_foreign` (`author_id`),
  KEY `news_created_by_foreign` (`created_by`),
  KEY `news_updated_by_foreign` (`updated_by`),
  KEY `news_category_id_index` (`category_id`),
  KEY `news_status_published_at_index` (`status`,`published_at`),
  KEY `news_is_featured_index` (`is_featured`),
  FULLTEXT KEY `news_title_excerpt_fulltext` (`title`,`excerpt`),
  CONSTRAINT `news_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `news_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.news: ~3 rows (approximately)
INSERT INTO `news` (`id`, `category_id`, `title`, `slug`, `excerpt`, `body`, `author_id`, `status`, `published_at`, `is_featured`, `views_count`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 2, 'Campus Wins National Innovation Award', 'campus-wins-national-innovation-award', 'A short introduction to "Campus Wins National Innovation Award".', '<p>This is the full body of <strong>Campus Wins National Innovation Award</strong>.</p>', 1, 'published', '2026-07-16 09:53:11', 1, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL),
	(2, 1, 'New Partnership With Regional Hospitals Announced', 'new-partnership-with-regional-hospitals-announced', 'A short introduction to "New Partnership With Regional Hospitals Announced".', '<p>This is the full body of <strong>New Partnership With Regional Hospitals Announced</strong>.</p>', 1, 'published', '2026-07-18 09:53:11', 0, 1, NULL, NULL, '2026-08-10 04:23:11', '2026-08-14 00:25:12', NULL),
	(3, 3, 'Faculty of Engineering Students Place First at Robotics Competition', 'faculty-of-engineering-students-place-first-at-robotics-competition', 'A short introduction to "Faculty of Engineering Students Place First at Robotics Competition".', '<p>This is the full body of <strong>Faculty of Engineering Students Place First at Robotics Competition</strong>.</p>', 1, 'published', '2026-07-28 09:53:11', 0, 0, NULL, NULL, '2026-08-10 04:23:11', '2026-08-10 04:23:11', NULL);

-- Dumping structure for table pn_knowledge_campus.news_categories
CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_categories_name_unique` (`name`),
  UNIQUE KEY `news_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.news_categories: ~3 rows (approximately)
INSERT INTO `news_categories` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Press Releases', 'press-releases', 0, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Announcements', 'announcements', 1, '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Achievements', 'achievements', 2, '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.notifications: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.office_hours
CREATE TABLE IF NOT EXISTS `office_hours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `day` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `opens_at` time DEFAULT NULL,
  `closes_at` time DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `office_hours_day_unique` (`day`),
  KEY `office_hours_created_by_foreign` (`created_by`),
  KEY `office_hours_updated_by_foreign` (`updated_by`),
  KEY `office_hours_order_index` (`order`),
  CONSTRAINT `office_hours_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `office_hours_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.office_hours: ~7 rows (approximately)
INSERT INTO `office_hours` (`id`, `day`, `is_open`, `opens_at`, `closes_at`, `note`, `order`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 'monday', 1, '08:30:00', '17:00:00', NULL, 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(2, 'tuesday', 1, '08:30:00', '17:00:00', NULL, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(3, 'wednesday', 1, '08:30:00', '17:00:00', NULL, 2, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(4, 'thursday', 1, '08:30:00', '17:00:00', NULL, 3, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(5, 'friday', 1, '08:30:00', '17:00:00', NULL, 4, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(6, 'saturday', 0, NULL, NULL, NULL, 5, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(7, 'sunday', 0, NULL, NULL, NULL, 6, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10');

-- Dumping structure for table pn_knowledge_campus.pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `status` enum('draft','published','scheduled','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_created_by_foreign` (`created_by`),
  KEY `pages_updated_by_foreign` (`updated_by`),
  KEY `pages_status_index` (`status`),
  CONSTRAINT `pages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pages_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.pages: ~13 rows (approximately)
INSERT INTO `pages` (`id`, `title`, `slug`, `template`, `status`, `published_at`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'About', 'about', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(2, 'Vision', 'vision', 'default', 'archived', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:12:42', NULL),
	(3, 'Mission', 'mission', 'default', 'archived', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:12:42', NULL),
	(4, 'Chairman\'s Message', 'chairmans-message', 'default', 'archived', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:12:42', NULL),
	(5, 'Admissions', 'admissions', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(6, 'How to Apply', 'how-to-apply', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(7, 'Scholarships', 'scholarships', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(8, 'International Students', 'international-students', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(9, 'Student Life', 'student-life', 'default', 'archived', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 01:12:42', NULL),
	(10, 'Career', 'career', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(11, 'Privacy Policy', 'privacy-policy', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(12, 'Terms & Conditions', 'terms', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(13, 'Refund Policy', 'refund-policy', 'default', 'published', '2026-08-10 09:53:10', NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL);

-- Dumping structure for table pn_knowledge_campus.page_blocks
CREATE TABLE IF NOT EXISTS `page_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint unsigned NOT NULL,
  `block_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_blocks_created_by_foreign` (`created_by`),
  KEY `page_blocks_updated_by_foreign` (`updated_by`),
  KEY `page_blocks_page_id_order_index` (`page_id`,`order`),
  CONSTRAINT `page_blocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `page_blocks_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_blocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.page_blocks: ~32 rows (approximately)
INSERT INTO `page_blocks` (`id`, `page_id`, `block_type`, `data`, `order`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(3, 2, 'hero', '{"heading": "Our Vision", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(4, 2, 'text', '{"body": "To be a globally recognized center of academic excellence, innovation, and character development."}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(5, 3, 'hero', '{"heading": "Our Mission", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(6, 3, 'text', '{"body": "To provide accessible, high-quality education that empowers students to lead and serve with integrity."}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(7, 4, 'hero', '{"heading": "Chairman\'s Message", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(10, 5, 'hero', '{"heading": "Admissions", "alignment": "center", "subheading": "Start your journey with us today."}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(11, 5, 'cta', '{"body": "Our admissions team is here to help at every step.", "style": "primary", "heading": "Ready to apply?", "button_url": "/contact", "button_label": "Contact Admissions"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(12, 5, 'faq', '{"items": [{"answer": "Admissions open twice a year; check the Admissions office for exact dates.", "question": "When does the admissions cycle open?"}, {"answer": "Academic transcripts, identification, and a completed application form.", "question": "What documents are required?"}]}', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(13, 6, 'hero', '{"heading": "How to Apply", "alignment": "center", "subheading": "Four simple steps to start your application."}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(14, 6, 'rich_text', '{"body": "<p>Applying to PNK Global Campus is straightforward — choose your course, submit your documents, and our Admissions team will guide you through the rest.</p>"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(15, 6, 'cta', '{"body": "Reach out to our Admissions team at any point in the process.", "style": "primary", "heading": "Have a question?", "button_url": "/contact", "button_label": "Contact Admissions"}', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(16, 7, 'hero', '{"heading": "Scholarships", "alignment": "center", "subheading": "Financial support for students who qualify."}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(17, 7, 'text', '{"body": "PNK Global Campus offers a range of merit- and need-based scholarships across our Faculties. Speak with Admissions to find out which ones you may be eligible for."}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(18, 7, 'faq', '{"items": [{"answer": "Scholarship applications open alongside each admissions cycle — apply at the same time as your course application.", "question": "When should I apply for a scholarship?"}]}', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(19, 8, 'hero', '{"heading": "International Students", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(20, 8, 'text', '{"body": "PNK Global Campus welcomes students from around the world, offering dedicated support for visas, housing, and orientation."}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(21, 8, 'faq', '{"items": [{"answer": "Yes, our International Office assists with visa applications and documentation.", "question": "Do you offer visa support?"}]}', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(22, 9, 'hero', '{"heading": "Student Life", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(23, 9, 'statistics', '{"items": [{"label": "Student Clubs", "value": "40+"}, {"label": "Annual Events", "value": "25+"}, {"label": "Sports Teams", "value": "12"}]}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(24, 9, 'text', '{"body": "From clubs and societies to sports and cultural events, campus life here is as enriching as the classroom."}', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(25, 10, 'hero', '{"heading": "Careers at PNK Global Campus", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(26, 10, 'cta', '{"body": "We are always looking for passionate educators and staff.", "style": "primary", "heading": "Join our team", "button_url": "/contact", "button_label": "View Openings"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(27, 11, 'hero', '{"heading": "Privacy Policy", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(28, 11, 'rich_text', '{"body": "<p>This Privacy Policy describes how PNK Global Campus collects, uses, and protects your personal information.</p>"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(29, 12, 'hero', '{"heading": "Terms & Conditions", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(30, 12, 'rich_text', '{"body": "<p>By using this website and our services, you agree to the following terms and conditions.</p>"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(31, 13, 'hero', '{"heading": "Refund Policy", "alignment": "center", "subheading": null}', 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(32, 13, 'rich_text', '{"body": "<p>This Refund Policy outlines the circumstances under which fees paid to PNK Global Campus may be refunded.</p>"}', 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(34, 4, 'chairman_message', '{"name": "A.G Prem Nawath", "role": "Chairman, PNK Global Campus", "message": "Let me welcome you to our website!\\n\\nAs we welcome our future leaders and productive citizens of the world, I want to highlight our mission of shaping young minds and hearts as the foundation for a nation\'s progress.\\n\\nAs a well-established institution, we uphold a high academic standard paired with strong discipline, helping us achieve consistently excellent results. We believe education is the key to a nation\'s progress, and it is our duty to give every student the best possible academic training to succeed in whatever career path they choose.\\n\\nWe are committed to supporting you in achieving your goals and ensuring your time here is meaningful and rewarding, with personal attention and care for every member of our campus community.\\n\\nI wish you all the very best!", "media_id": 7}', 1, 1, NULL, NULL, '2026-08-14 03:17:37', '2026-08-14 03:30:19'),
	(35, 1, 'hero', '{"heading": "About PNK Global Campus", "alignment": "center", "subheading": "Building futures through knowledge, character, and community."}', 0, 1, NULL, NULL, '2026-08-17 01:12:39', '2026-08-17 01:12:39'),
	(36, 1, 'rich_text', '{"body": "<p>PNK Global Campus has served students and the wider community for years, combining rigorous academics with a supportive campus life.</p>"}', 1, 1, NULL, NULL, '2026-08-17 01:12:42', '2026-08-17 01:12:42'),
	(37, 1, 'text', '{"body": "Our Vision\\n\\nTo be a globally recognized center of academic excellence, innovation, and character development."}', 2, 1, NULL, NULL, '2026-08-17 01:12:42', '2026-08-17 01:12:42'),
	(38, 1, 'text', '{"body": "Our Mission\\n\\nTo provide accessible, high-quality education that empowers students to lead and serve with integrity."}', 3, 1, NULL, NULL, '2026-08-17 01:12:42', '2026-08-17 01:12:42'),
	(39, 1, 'chairman_message', '{"name": "A.G Prem Nawath", "role": "Chairman, PNK Global Campus", "message": "Let me welcome you to our website!\\n\\nAs we welcome our future leaders and productive citizens of the world, I want to highlight our mission of shaping young minds and hearts as the foundation for a nation\'s progress.\\n\\nAs a well-established institution, we uphold a high academic standard paired with strong discipline, helping us achieve consistently excellent results. We believe education is the key to a nation\'s progress, and it is our duty to give every student the best possible academic training to succeed in whatever career path they choose.\\n\\nWe are committed to supporting you in achieving your goals and ensuring your time here is meaningful and rewarding, with personal attention and care for every member of our campus community.\\n\\nI wish you all the very best!", "media_id": 7}', 4, 1, NULL, NULL, '2026-08-17 01:12:42', '2026-08-17 01:12:42'),
	(40, 1, 'chairman_message', '{"name": "R. Rahupathy", "role": "Manager, PNK Global Campus", "message": "Welcome to PNK Global Campus!\\n\\nAs the Manager of this campus, I take pride in ensuring that every student\'s daily experience here reflects our commitment to quality education and genuine care. Behind every lecture, every event, and every service on campus is a team working to make your time here smooth, supportive, and productive.\\n\\nWe believe that a well-run campus is the foundation for real learning — that means responsive administration, accessible faculty, and a safe, organized environment where students can focus on what matters most: growing academically and personally.\\n\\nMy door is always open to student concerns and ideas. We are continuously working to improve our facilities and services, and your feedback plays a real part in that process.\\n\\nI look forward to supporting you throughout your journey with us.", "media_id": 8}', 5, 1, NULL, NULL, '2026-08-17 01:12:42', '2026-08-17 01:12:42');

-- Dumping structure for table pn_knowledge_campus.page_views
CREATE TABLE IF NOT EXISTS `page_views` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visitor_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_views_created_at_index` (`created_at`),
  KEY `page_views_path_index` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.page_views: ~255 rows (approximately)
INSERT INTO `page_views` (`id`, `path`, `visitor_id`, `created_at`) VALUES
	(1, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:36:48'),
	(2, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:36:49'),
	(3, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:36:50'),
	(4, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:12'),
	(5, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:14'),
	(6, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:15'),
	(7, '/departments/department-of-marketing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:16'),
	(8, '/faq', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:20'),
	(9, '/departments/department-of-marketing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:21'),
	(10, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:22'),
	(11, '/privacy-policy', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:25'),
	(12, '/blog', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:34'),
	(13, '/blog/five-scholarships-every-new-student-should-know-about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:46'),
	(14, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:54'),
	(15, '/courses/bsc-hons-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:38:58'),
	(16, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-10 04:39:30'),
	(17, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:21:24'),
	(18, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:21:24'),
	(19, '/vision', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:21:47'),
	(20, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:21:52'),
	(21, '/scholarships', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:22:04'),
	(22, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:22:15'),
	(23, '/events/open-day-2026', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:22:27'),
	(24, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:22:29'),
	(25, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:25:01'),
	(26, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 00:25:01'),
	(27, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 09:57:18'),
	(28, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 09:57:19'),
	(29, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:23:11'),
	(30, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:23:12'),
	(31, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:27:39'),
	(32, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:27:40'),
	(33, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:28:51'),
	(34, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:28:52'),
	(35, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:28:58'),
	(36, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:28:59'),
	(37, '/courses/bsc-hons-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:29:11'),
	(38, '/courses/bsc-hons-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:29:12'),
	(39, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:29:18'),
	(40, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:29:20'),
	(41, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 10:29:21'),
	(42, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 23:50:25'),
	(43, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 23:50:26'),
	(44, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 23:50:36'),
	(45, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-13 23:50:37'),
	(46, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:05:09'),
	(47, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:05:10'),
	(48, '/vision', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:07:27'),
	(49, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:07:31'),
	(50, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:23:20'),
	(51, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:23:20'),
	(52, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:23:25'),
	(53, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:24:07'),
	(54, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:24:33'),
	(55, '/news/new-partnership-with-regional-hospitals-announced', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:25:13'),
	(56, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:25:15'),
	(57, '/courses/bsc-hons-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:28:15'),
	(58, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:28:17'),
	(59, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:34:16'),
	(60, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 00:34:17'),
	(61, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:03:18'),
	(62, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:03:19'),
	(63, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:04:09'),
	(64, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:04:21'),
	(65, '/courses/diploma-in-quantity-surveying', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:04:29'),
	(66, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:06:03'),
	(67, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:06:05'),
	(68, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:06:06'),
	(69, '/admissions', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:06:13'),
	(70, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:06:27'),
	(71, '/faculties/faculty-of-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:22:48'),
	(72, '/faculties/faculty-of-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:22:50'),
	(73, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:23:03'),
	(74, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:32:53'),
	(75, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:32:55'),
	(76, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:52:37'),
	(77, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:52:39'),
	(78, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:52:53'),
	(79, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 01:52:56'),
	(80, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:08:03'),
	(81, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:08:05'),
	(82, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:16:18'),
	(83, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:16:19'),
	(84, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:17:33'),
	(85, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:17:45'),
	(86, '/events/annual-tech-symposium', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:22:14'),
	(87, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:22:22'),
	(88, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:24:21'),
	(89, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:24:22'),
	(90, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:31:47'),
	(91, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:31:49'),
	(92, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:40:03'),
	(93, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:40:05'),
	(94, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:47:29'),
	(95, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:47:31'),
	(96, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:47:57'),
	(97, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 02:47:59'),
	(98, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:08:29'),
	(99, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:08:31'),
	(100, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:16:58'),
	(101, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:17:00'),
	(102, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:18:22'),
	(103, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:18:24'),
	(104, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:26:16'),
	(105, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 03:26:19'),
	(106, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 04:32:14'),
	(107, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 04:32:15'),
	(108, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 04:50:23'),
	(109, '/chairmans-message', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-14 04:50:24'),
	(110, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 22:59:27'),
	(111, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 22:59:29'),
	(112, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 23:00:25'),
	(113, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 23:00:25'),
	(114, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 23:56:33'),
	(115, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-16 23:56:34'),
	(116, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:11:31'),
	(117, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:11:32'),
	(118, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:11:48'),
	(119, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:17:07'),
	(120, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:17:10'),
	(121, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:17:22'),
	(122, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:17:25'),
	(123, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:19:36'),
	(124, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:19:36'),
	(125, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:19:50'),
	(126, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:19:59'),
	(127, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:20:02'),
	(128, '/student-life', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 00:34:37'),
	(129, '/student-life', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:15:41'),
	(130, '/student-life', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:15:41'),
	(131, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:16:40'),
	(132, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:16:42'),
	(133, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:24:53'),
	(134, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:24:54'),
	(135, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:27:33'),
	(136, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:27:34'),
	(137, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:31:43'),
	(138, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:31:43'),
	(139, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:32:03'),
	(140, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:14'),
	(141, '/departments/department-of-civil-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:28'),
	(142, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:32'),
	(143, '/departments/department-of-electrical-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:33'),
	(144, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:48'),
	(145, '/departments/department-of-mechanical-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:33:50'),
	(146, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:08'),
	(147, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:09'),
	(148, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:13'),
	(149, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:17'),
	(150, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:17'),
	(151, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:24'),
	(152, '/faculties/faculty-of-business', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:28'),
	(153, '/departments/department-of-accounting-finance', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:32'),
	(154, '/faculties/faculty-of-business', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:35'),
	(155, '/departments/department-of-marketing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:34:36'),
	(156, '/departments/department-of-marketing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:12'),
	(157, '/departments/department-of-marketing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:12'),
	(158, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:14'),
	(159, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:17'),
	(160, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:17'),
	(161, '/faculties/faculty-of-health-sciences', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:27'),
	(162, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:32'),
	(163, '/faculties/faculty-of-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:34'),
	(164, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:37'),
	(165, '/faculties/faculty-of-arts', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:39'),
	(166, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:42'),
	(167, '/faculties/faculty-of-computing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:45'),
	(168, '/departments/department-of-software-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:35:49'),
	(169, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:36:08'),
	(170, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:36:12'),
	(171, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:36:13'),
	(172, '/departments/department-of-civil-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:36:23'),
	(173, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 01:36:30'),
	(174, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:03:13'),
	(175, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:03:14'),
	(176, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:08:11'),
	(177, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:08:12'),
	(178, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:00'),
	(179, '/faculties', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:00'),
	(180, '/faculties/faculty-of-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:04'),
	(181, '/departments/department-of-early-childhood-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:10'),
	(182, '/courses/diploma-in-early-childhood-care-and-primary-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:13'),
	(183, '/departments/department-of-early-childhood-education', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:25'),
	(184, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:27'),
	(185, '/courses/certificate-in-information-and-communication-technology', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:37'),
	(186, '/how-to-apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:42'),
	(187, '/international-students', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:09:48'),
	(188, '/faq', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:00'),
	(189, '/downloads', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:13'),
	(190, '/career', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:22'),
	(191, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:25'),
	(192, '/career', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:30'),
	(193, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:36'),
	(194, '/career', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:10:50'),
	(195, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:11:01'),
	(196, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:24:01'),
	(197, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 02:24:02'),
	(198, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:07:47'),
	(199, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:07:48'),
	(200, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:25:04'),
	(201, '/apply/resume', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:27:25'),
	(202, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:27:34'),
	(203, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:28:26'),
	(204, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:28:28'),
	(205, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:50:12'),
	(206, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:50:14'),
	(207, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:06'),
	(208, '/faculties/faculty-of-computing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:23'),
	(209, '/departments/department-of-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:33'),
	(210, '/faculties/faculty-of-computing', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:41'),
	(211, '/courses', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:54'),
	(212, '/departments/department-of-computer-science', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:51:57'),
	(213, '/faculties/faculty-of-engineering', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:52:17'),
	(214, '/news', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:53:03'),
	(215, '/career', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:53:23'),
	(216, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:53:34'),
	(217, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:53:52'),
	(218, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:57:09'),
	(219, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 03:57:10'),
	(220, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 04:05:34'),
	(221, '/contact', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 04:05:34'),
	(222, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:44:19'),
	(223, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:44:27'),
	(224, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:44:54'),
	(225, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:44:56'),
	(226, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:44:59'),
	(227, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:45:00'),
	(228, '/career', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 05:47:46'),
	(229, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 22:29:56'),
	(230, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 22:29:58'),
	(231, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:00:37'),
	(232, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:01:32'),
	(233, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:01:34'),
	(234, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:01:48'),
	(235, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:11:40'),
	(236, '/about', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:11:42'),
	(237, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:12:55'),
	(238, '/student-life/registration', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:13:02'),
	(239, '/student-life/examination', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:13:04'),
	(240, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:13:12'),
	(241, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:15:04'),
	(242, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:15:07'),
	(243, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:20:03'),
	(244, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:20:06'),
	(245, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:22:03'),
	(246, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:22:28'),
	(247, '/apply', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:22:31'),
	(248, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:23:21'),
	(249, '/student-life/examination', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:23:24'),
	(250, '/student-life/certificate-verification', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:23:28'),
	(251, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:23:31'),
	(252, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:28:15'),
	(253, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:28:17'),
	(254, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:29:13'),
	(255, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:29:14'),
	(256, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:29:32'),
	(257, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:29:34'),
	(258, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:30:33'),
	(259, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:30:36'),
	(260, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:30:51'),
	(261, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:30:54'),
	(262, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:33:34'),
	(263, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:33:38'),
	(264, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:38:07'),
	(265, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:38:09'),
	(266, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:41:25'),
	(267, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-17 23:41:27'),
	(268, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-18 01:27:29'),
	(269, '/', 'f49a3867-6393-4d2d-b0e6-4e5276d23c76', '2026-08-18 01:27:29');

-- Dumping structure for table pn_knowledge_campus.partners
CREATE TABLE IF NOT EXISTS `partners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partners_created_by_foreign` (`created_by`),
  KEY `partners_updated_by_foreign` (`updated_by`),
  KEY `partners_is_active_order_index` (`is_active`,`order`),
  KEY `partners_category_id_foreign` (`category_id`),
  CONSTRAINT `partners_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `partner_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `partners_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `partners_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.partners: ~4 rows (approximately)
INSERT INTO `partners` (`id`, `category_id`, `name`, `url`, `order`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 3, 'National Accreditation Board', NULL, 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(2, 3, 'Ministry of Education', NULL, 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(3, 1, 'International Education Alliance', 'https://example.org', 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(4, 2, 'TechCorp Solutions', 'https://example.org', 3, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL);

-- Dumping structure for table pn_knowledge_campus.partner_categories
CREATE TABLE IF NOT EXISTS `partner_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_categories_name_unique` (`name`),
  UNIQUE KEY `partner_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.partner_categories: ~3 rows (approximately)
INSERT INTO `partner_categories` (`id`, `name`, `slug`, `order`, `created_at`, `updated_at`) VALUES
	(1, 'Academic Partner', 'academic-partner', 0, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(2, 'Industry Partner', 'industry-partner', 1, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(3, 'Accreditation Body', 'accreditation-body', 2, '2026-08-10 04:23:10', '2026-08-10 04:23:10');

-- Dumping structure for table pn_knowledge_campus.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.permissions: ~83 rows (approximately)
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'users.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(2, 'users.create', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(3, 'users.edit', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(4, 'users.delete', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(5, 'roles.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(6, 'roles.create', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(7, 'roles.edit', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(8, 'roles.delete', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(9, 'dashboard.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(10, 'settings.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(11, 'settings.edit', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(12, 'media.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(13, 'media.create', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(14, 'media.edit', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(15, 'media.delete', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(16, 'seo.view', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(17, 'seo.edit', 'sanctum', '2026-08-10 04:23:08', '2026-08-10 04:23:08'),
	(18, 'menus.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(19, 'menus.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(20, 'pages.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(21, 'pages.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(22, 'pages.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(23, 'pages.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(24, 'pages.publish', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(25, 'homepage.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(26, 'homepage.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(27, 'hero_slides.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(28, 'hero_slides.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(29, 'hero_slides.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(30, 'hero_slides.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(31, 'testimonials.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(32, 'testimonials.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(33, 'testimonials.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(34, 'testimonials.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(35, 'partners.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(36, 'partners.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(37, 'partners.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(38, 'partners.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(39, 'faculties.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(40, 'faculties.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(41, 'faculties.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(42, 'faculties.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(43, 'departments.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(44, 'departments.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(45, 'departments.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(46, 'departments.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(47, 'courses.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(48, 'courses.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(49, 'courses.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(50, 'courses.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(51, 'courses.publish', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(52, 'blog.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(53, 'blog.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(54, 'blog.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(55, 'blog.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(56, 'blog.publish', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(57, 'news.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(58, 'news.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(59, 'news.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(60, 'news.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(61, 'news.publish', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(62, 'events.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(63, 'events.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(64, 'events.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(65, 'events.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(66, 'gallery.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(67, 'gallery.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(68, 'gallery.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(69, 'gallery.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(70, 'faq.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(71, 'faq.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(72, 'faq.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(73, 'faq.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(74, 'downloads.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(75, 'downloads.create', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(76, 'downloads.edit', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(77, 'downloads.delete', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(78, 'applications.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(79, 'applications.review', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(80, 'applications.export', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(81, 'inquiries.view', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(82, 'inquiries.manage', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(83, 'inquiries.export', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09');

-- Dumping structure for table pn_knowledge_campus.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.personal_access_tokens: ~1 rows (approximately)
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
	(1, 'user', 1, 'CMS Web Login', 'c72cc3b7d7658fbbe68db078f9bfb2ada8adda54ba65e6bbab6f3dae7d9b77af', '["*"]', '2026-08-18 01:42:55', NULL, '2026-08-14 00:39:18', '2026-08-18 01:42:55');

-- Dumping structure for table pn_knowledge_campus.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.roles: ~5 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'Super Admin', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(2, 'Administrator', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(3, 'Content Editor', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(4, 'Marketing', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09'),
	(5, 'Admissions', 'sanctum', '2026-08-10 04:23:09', '2026-08-10 04:23:09');

-- Dumping structure for table pn_knowledge_campus.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.role_has_permissions: ~252 rows (approximately)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1),
	(6, 1),
	(7, 1),
	(8, 1),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(13, 1),
	(14, 1),
	(15, 1),
	(16, 1),
	(17, 1),
	(18, 1),
	(19, 1),
	(20, 1),
	(21, 1),
	(22, 1),
	(23, 1),
	(24, 1),
	(25, 1),
	(26, 1),
	(27, 1),
	(28, 1),
	(29, 1),
	(30, 1),
	(31, 1),
	(32, 1),
	(33, 1),
	(34, 1),
	(35, 1),
	(36, 1),
	(37, 1),
	(38, 1),
	(39, 1),
	(40, 1),
	(41, 1),
	(42, 1),
	(43, 1),
	(44, 1),
	(45, 1),
	(46, 1),
	(47, 1),
	(48, 1),
	(49, 1),
	(50, 1),
	(51, 1),
	(52, 1),
	(53, 1),
	(54, 1),
	(55, 1),
	(56, 1),
	(57, 1),
	(58, 1),
	(59, 1),
	(60, 1),
	(61, 1),
	(62, 1),
	(63, 1),
	(64, 1),
	(65, 1),
	(66, 1),
	(67, 1),
	(68, 1),
	(69, 1),
	(70, 1),
	(71, 1),
	(72, 1),
	(73, 1),
	(74, 1),
	(75, 1),
	(76, 1),
	(77, 1),
	(78, 1),
	(79, 1),
	(80, 1),
	(81, 1),
	(82, 1),
	(83, 1),
	(9, 2),
	(12, 2),
	(13, 2),
	(14, 2),
	(15, 2),
	(16, 2),
	(17, 2),
	(18, 2),
	(19, 2),
	(20, 2),
	(21, 2),
	(22, 2),
	(23, 2),
	(24, 2),
	(25, 2),
	(26, 2),
	(27, 2),
	(28, 2),
	(29, 2),
	(30, 2),
	(31, 2),
	(32, 2),
	(33, 2),
	(34, 2),
	(35, 2),
	(36, 2),
	(37, 2),
	(38, 2),
	(39, 2),
	(40, 2),
	(41, 2),
	(42, 2),
	(43, 2),
	(44, 2),
	(45, 2),
	(46, 2),
	(47, 2),
	(48, 2),
	(49, 2),
	(50, 2),
	(51, 2),
	(52, 2),
	(53, 2),
	(54, 2),
	(55, 2),
	(56, 2),
	(57, 2),
	(58, 2),
	(59, 2),
	(60, 2),
	(61, 2),
	(62, 2),
	(63, 2),
	(64, 2),
	(65, 2),
	(66, 2),
	(67, 2),
	(68, 2),
	(69, 2),
	(70, 2),
	(71, 2),
	(72, 2),
	(73, 2),
	(74, 2),
	(75, 2),
	(76, 2),
	(77, 2),
	(78, 2),
	(79, 2),
	(80, 2),
	(81, 2),
	(82, 2),
	(83, 2),
	(9, 3),
	(12, 3),
	(13, 3),
	(14, 3),
	(16, 3),
	(17, 3),
	(20, 3),
	(21, 3),
	(22, 3),
	(31, 3),
	(32, 3),
	(33, 3),
	(39, 3),
	(40, 3),
	(41, 3),
	(43, 3),
	(44, 3),
	(45, 3),
	(47, 3),
	(48, 3),
	(49, 3),
	(52, 3),
	(53, 3),
	(54, 3),
	(57, 3),
	(58, 3),
	(59, 3),
	(62, 3),
	(63, 3),
	(64, 3),
	(66, 3),
	(67, 3),
	(68, 3),
	(70, 3),
	(71, 3),
	(72, 3),
	(74, 3),
	(75, 3),
	(76, 3),
	(9, 4),
	(12, 4),
	(13, 4),
	(14, 4),
	(16, 4),
	(17, 4),
	(20, 4),
	(25, 4),
	(26, 4),
	(27, 4),
	(28, 4),
	(29, 4),
	(31, 4),
	(32, 4),
	(33, 4),
	(35, 4),
	(36, 4),
	(37, 4),
	(39, 4),
	(43, 4),
	(47, 4),
	(52, 4),
	(53, 4),
	(54, 4),
	(57, 4),
	(58, 4),
	(59, 4),
	(62, 4),
	(63, 4),
	(64, 4),
	(66, 4),
	(67, 4),
	(68, 4),
	(70, 4),
	(74, 4),
	(75, 4),
	(76, 4),
	(81, 4),
	(9, 5),
	(12, 5),
	(39, 5),
	(43, 5),
	(47, 5),
	(48, 5),
	(49, 5),
	(70, 5),
	(71, 5),
	(72, 5),
	(74, 5),
	(75, 5),
	(76, 5),
	(78, 5),
	(79, 5),
	(80, 5),
	(81, 5),
	(82, 5),
	(83, 5);

-- Dumping structure for table pn_knowledge_campus.seo_meta
CREATE TABLE IF NOT EXISTS `seo_meta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seoable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seoable_id` bigint unsigned NOT NULL,
  `seo_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_type` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image_media_id` bigint unsigned DEFAULT NULL,
  `twitter_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image_media_id` bigint unsigned DEFAULT NULL,
  `robots_index` tinyint(1) NOT NULL DEFAULT '1',
  `robots_follow` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_meta_seoable_type_seoable_id_unique` (`seoable_type`,`seoable_id`),
  KEY `seo_meta_created_by_foreign` (`created_by`),
  KEY `seo_meta_updated_by_foreign` (`updated_by`),
  CONSTRAINT `seo_meta_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seo_meta_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.seo_meta: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.sessions: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_created_by_foreign` (`created_by`),
  KEY `settings_updated_by_foreign` (`updated_by`),
  KEY `settings_group_index` (`group`),
  CONSTRAINT `settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.settings: ~42 rows (approximately)
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `is_public`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 'campus_name', NULL, 'campus', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(2, 'campus_short_name', NULL, 'campus', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(3, 'campus_tagline', NULL, 'campus', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(4, 'registration_number', NULL, 'campus', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(5, 'accreditation_number', NULL, 'campus', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(6, 'contact_email', 'info@pnkgcampus.com, pnkgc.sl@gmail.com', 'contact', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:50'),
	(7, 'contact_phone', '0771981327, 0771981447', 'contact', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:50'),
	(8, 'contact_address', '635, Masjithul Islam Nagar, Maruthamunai-03, Kalmunai, Sri Lanka', 'contact', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:50'),
	(9, 'admissions_email', NULL, 'contact', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:50'),
	(10, 'admissions_phone', NULL, 'contact', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:50'),
	(11, 'google_maps_embed_url', NULL, 'maps', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(12, 'google_maps_api_key', NULL, 'maps', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(13, 'smtp_host', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(14, 'smtp_port', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(15, 'smtp_username', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(16, 'smtp_password', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(17, 'smtp_encryption', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(18, 'mail_from_address', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(19, 'mail_from_name', NULL, 'smtp', 0, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(20, 'logo_media_id', '3', 'branding', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:27:40'),
	(21, 'favicon_media_id', '3', 'branding', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:27:55'),
	(22, 'header_logo_height', NULL, 'branding', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:00'),
	(23, 'footer_logo_height', NULL, 'branding', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-17 23:28:00'),
	(24, 'footer_text', NULL, 'footer', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(25, 'footer_copyright', NULL, 'footer', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(26, 'ga_tracking_id', NULL, 'analytics', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(27, 'gtm_container_id', NULL, 'analytics', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(28, 'site_url', NULL, 'seo_defaults', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(29, 'default_meta_title', NULL, 'seo_defaults', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(30, 'default_meta_description', NULL, 'seo_defaults', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(31, 'default_keywords', NULL, 'seo_defaults', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(32, 'default_og_image_media_id', NULL, 'seo_defaults', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(33, 'welcome_heading', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(34, 'welcome_body', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(35, 'welcome_media_id', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(36, 'why_choose_us_items', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(37, 'statistics_items', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(38, 'cta_heading', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(39, 'cta_body', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(40, 'cta_button_label', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(41, 'cta_button_url', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10'),
	(42, 'footer_widgets', NULL, 'homepage', 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10');

-- Dumping structure for table pn_knowledge_campus.social_links
CREATE TABLE IF NOT EXISTS `social_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_links_created_by_foreign` (`created_by`),
  KEY `social_links_updated_by_foreign` (`updated_by`),
  KEY `social_links_is_active_order_index` (`is_active`,`order`),
  CONSTRAINT `social_links_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `social_links_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.social_links: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.taggables
CREATE TABLE IF NOT EXISTS `taggables` (
  `tag_id` bigint unsigned NOT NULL,
  `taggable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taggable_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`taggable_type`,`taggable_id`),
  KEY `taggables_taggable_type_taggable_id_index` (`taggable_type`,`taggable_id`),
  CONSTRAINT `taggables_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.taggables: ~4 rows (approximately)
INSERT INTO `taggables` (`tag_id`, `taggable_type`, `taggable_id`) VALUES
	(3, 'blog_post', 1),
	(1, 'blog_post', 2),
	(2, 'blog_post', 2),
	(4, 'blog_post', 3);

-- Dumping structure for table pn_knowledge_campus.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.tags: ~4 rows (approximately)
INSERT INTO `tags` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
	(1, 'Admissions', 'admissions', '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(2, 'Scholarships', 'scholarships', '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(3, 'Events', 'events', '2026-08-10 04:23:11', '2026-08-10 04:23:11'),
	(4, 'Research', 'research', '2026-08-10 04:23:11', '2026-08-10 04:23:11');

-- Dumping structure for table pn_knowledge_campus.testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `testimonials_created_by_foreign` (`created_by`),
  KEY `testimonials_updated_by_foreign` (`updated_by`),
  KEY `testimonials_course_id_index` (`course_id`),
  KEY `testimonials_is_featured_order_index` (`is_featured`,`order`),
  KEY `testimonials_is_active_index` (`is_active`),
  CONSTRAINT `testimonials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `testimonials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `testimonials_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.testimonials: ~3 rows (approximately)
INSERT INTO `testimonials` (`id`, `name`, `role_title`, `course_id`, `content`, `rating`, `is_featured`, `order`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Aisha Rahman', 'BSc Graduate, 2024', NULL, 'The faculty here pushed me to think beyond the syllabus. I left more confident and more capable than I ever expected.', 5, 1, 0, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(2, 'Daniel Osei', 'MSc Graduate, 2023', NULL, 'Small class sizes meant real mentorship, not just lectures. That made all the difference.', 5, 1, 1, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL),
	(3, 'Priya Nair', 'Current Student', NULL, 'From day one, campus life felt like a community, not just a school.', 4, 1, 2, 1, NULL, NULL, '2026-08-10 04:23:10', '2026-08-10 04:23:10', NULL);

-- Dumping structure for table pn_knowledge_campus.url_redirects
CREATE TABLE IF NOT EXISTS `url_redirects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_type` smallint NOT NULL DEFAULT '301',
  `hit_count` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_redirects_from_path_unique` (`from_path`),
  KEY `url_redirects_created_by_foreign` (`created_by`),
  KEY `url_redirects_updated_by_foreign` (`updated_by`),
  KEY `url_redirects_is_active_index` (`is_active`),
  CONSTRAINT `url_redirects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `url_redirects_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.url_redirects: ~0 rows (approximately)

-- Dumping structure for table pn_knowledge_campus.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_created_by_foreign` (`created_by`),
  KEY `users_updated_by_foreign` (`updated_by`),
  CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pn_knowledge_campus.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `is_active`, `last_login_at`, `created_by`, `updated_by`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Super Admin', 'superadmin@pnknowledgecampus.edu', '2026-08-10 04:23:10', '$2y$12$5RQYk1uoB02WNZw9G5reU.guBpz1xpzSVUQCbOlbV2NWWKK89xA1y', NULL, 1, '2026-08-14 00:39:18', NULL, NULL, NULL, '2026-08-10 04:23:10', '2026-08-14 00:39:18');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
