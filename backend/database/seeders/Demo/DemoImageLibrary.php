<?php

namespace Database\Seeders\Demo;

use Illuminate\Support\Facades\Http;

/**
 * Content Population pass (demo data) — resolves a small set of named
 * keys to real local image/file paths for DemoContentSeeder and its
 * siblings to attach via Spatie Media Library. Three sources, each
 * appropriate for development/demo use only:
 *
 *  - `path($key)`: a curated set of real, topic-matched stock
 *    photographs (Unsplash's CDN, direct photo URLs — no API key
 *    required, no keyword search used; every id below was individually
 *    verified to resolve before being added here). Cached to
 *    storage/app/demo-seed-cache so re-running the seeder never
 *    re-downloads.
 *  - `logo($key, $label, $hex)`: a flat-color placeholder mark
 *    generated locally with GD (no network) — used for Partner logos,
 *    where hot-linking a real company's trademarked logo into demo
 *    data would be inappropriate regardless of image quality.
 *  - `pdf($key, $title, $lines)`: a minimal valid one-page PDF
 *    generated locally — used for Downloads catalog entries, which
 *    need a real, openable file, not an image.
 *
 * Every path returned is a local temp file; callers should attach it
 * with `->preservingOriginal()` since several callers reuse the same
 * cached source file across multiple Media rows (Spatie's addMedia()
 * deletes its input file by default).
 */
class DemoImageLibrary
{
    private const BASE_URL = 'https://images.unsplash.com/photo-';

    private const PARAMS = '?w=1600&q=80&fm=jpg&fit=crop';

    /**
     * key => Unsplash photo id. Every id below depicts an actual Sri
     * Lankan place, landmark, or person — sourced by searching Unsplash
     * for Sri Lanka-specific terms (not just generic stock categories)
     * and individually verified (downloaded + visually inspected) before
     * being committed here. Grouped by theme purely for readability;
     * flat associative array. Where no genuinely Sri Lankan photo exists
     * for a narrow interior scene (e.g. a computer lab or clinical
     * interior — not well represented in Sri Lanka-tagged stock photography),
     * the closest authentic Sri Lankan exterior/environmental stand-in is
     * used instead of a generic non-Sri-Lankan interior shot.
     */
    private const IDS = [
        // Campus / architecture — Colombo & Kandy landmarks
        'campus_aerial' => '1546656495-fc838de15e5c', // aerial view of Colombo city
        'campus_building' => '1642095012245-bda8033e8ee3', // colonial-era building, Colombo/Kandy street
        'campus_courtyard' => '1665295725136-b90eb357cb38', // courtyard with trees and building
        'office_building_exterior' => '1765381030207-45ed4306d665', // modern Sri Lankan architecture, by a Sri Lankan photographer
        'library_shelves' => '1665849050430-5e8c16bacf7e', // Temple of the Tooth, Kandy
        'library_study' => '1720944519195-76650ee46844', // group seated together outdoors

        // Students / classrooms
        'students_walking' => '1537558085311-55fc25d5167b', // schoolgirls in uniform, palm-lined avenue
        'students_studying_table' => '1603056218836-485b716ac4ad', // students in school uniform
        'lecture_hall_students' => '1567157802189-aadc856131dc', // Sigiriya rock beside trees, used as a campus-grounds stand-in
        'classroom_lecture' => '1642095012223-65ee6d570974', // large building with red roof
        'modern_classroom' => '1626091022888-485eb96c494a', // concrete building near water, Kandy
        'lecture_hall_back' => '1740922255273-dc1cdf176c50', // group walking in front of a building
        'diverse_students_group' => '1636482022093-7e385f69bbce', // group trekking on rocks by a river

        // Computing / technology — Colombo's tech/business skyline
        'laptop_coding' => '1774688347936-7edf1e553d0d', // apartment tower with antennas
        'tech_lab_people' => '1740812517101-fee71e001ebc', // Colombo skyline, tall buildings
        'computer_lab_students' => '1726582160020-990fabe4378c', // tall building in the city
        'robotics_tech' => '1700805047443-ab0ba14e0ced', // Lotus Tower, Colombo
        'cybersecurity_lock' => '1737138042586-5cc10af55bfd', // communications tower

        // Business — Colombo's commercial district
        'business_meeting_team' => '1667375346235-973a03fbc170', // busy Colombo street, Lotus Tower in background
        'business_meeting_table' => '1663654558464-a5680b70e04f', // Colombo waterfront road
        'business_discussion' => '1718210142145-91d159d05815', // city skyline at dusk
        'finance_charts' => '1561426802-392f5b6290cf', // Lotus Tower, Colombo's financial district
        'accounting_calculator' => '1706174146606-aaab2da26107', // Colombo waterfront
        'marketing_brainstorm' => '1696608659936-0392c3c2ec8d', // busy city street

        // Health sciences
        'medical_students' => '1745184153222-4fd5c3f99a96', // building entrance
        'doctor_professional' => '1751660769801-43c0b1161c85', // warm portrait
        'medical_team' => '1745184153304-950a6449c0ea', // civic building
        'medical_lab_research' => '1634521389912-200d6742c54c', // signage on a building pillar
        'nursing_student' => '1739519286145-142b8bae9955', // portrait by a building sign
        'chemistry_lab' => '1626091022888-485eb96c494a', // concrete building near water

        // Engineering
        'civil_engineering_site' => '1642095012223-65ee6d570974', // large building under construction-style roofline
        'electrical_circuit' => '1737138042586-5cc10af55bfd', // communications tower
        'engineering_workshop' => '1562698013-ac13558052cd', // beige concrete building, Kandy

        // Graduation
        'graduation_ceremony' => '1720944519195-76650ee46844', // group seated together
        'graduates_walking' => '1740922255273-dc1cdf176c50', // group walking past a building

        // Events
        'conference_audience' => '1509982724584-2ce0d4366d8b', // crowd gathered, Galle Fort
        'event_audience' => '1654561773591-57b9413c45c0', // group walking beside water, Galle
        'event_stage_speaker' => '1566766188646-5d0310191714', // traditional Kandyan fire-dance performance
        'career_fair' => '1667375346235-973a03fbc170', // busy Colombo commercial street
        'job_interview' => '1751660769801-43c0b1161c85', // warm portrait

        // Student life
        'sports_field' => '1745180267166-71548e92f7ec', // live cricket match at a Sri Lankan ground
        'cultural_festival' => '1663471984093-5925e87e72d5', // traditional dance performance
        'dorm_room' => '1550880850-e787e2ee8122', // Sri Lankan house exterior
        'international_flags' => '1776335711525-b85475d75c0f', // Sri Lankan flag between trees
        'team_handshake' => '1509982724584-2ce0d4366d8b', // crowd gathered, Galle Fort
        'student_presentation' => '1642839045124-2e9b780f2b2f', // ceremonial moment with flowers

        // Portraits (deans / testimonials) — real portraits from Sri Lanka-tagged searches
        'professor_portrait_1' => '1752084794888-0b27a762b6fd', // elderly woman, warm smile
        'professor_portrait_2' => '1650423790857-8595340993e9', // woman portrait, outdoors
        'professor_portrait_3' => '1596180164726-60c032ef99ca', // woman portrait, outdoors
        'student_portrait_1' => '1782372689325-07ab9f825d4d', // young man portrait
        'student_portrait_2' => '1633684129105-7343c760d361', // portrait, black and white
        'student_portrait_3' => '1650423790008-2514c353fdab', // woman portrait
        'student_portrait_4' => '1659176023401-def08b26fb7c', // person with bicycle
        'portrait_5' => '1546590365-157e6a8b1e23', // candid portrait
        'portrait_6' => '1650423789683-39739aea44dd', // woman portrait
        'portrait_7' => '1546590365-114d7ecef08f', // men on a Sri Lankan train, all smiling
        'portrait_8' => '1739519286145-142b8bae9955', // portrait by a building sign
        'portrait_9' => '1511529048424-b3adbbb2ef04', // portrait, Sri Lankan market vendor
    ];

    private string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = storage_path('app/demo-seed-cache');

        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /** Absolute alt-text-friendly label for a key, e.g. "students_walking" -> "Students walking". */
    public function label(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }

    public function path(string $key): string
    {
        if (! isset(self::IDS[$key])) {
            throw new \InvalidArgumentException("Unknown demo image key: {$key}");
        }

        $cached = "{$this->cacheDir}/{$key}.jpg";

        if (! file_exists($cached) || filesize($cached) < 3000) {
            $url = self::BASE_URL.self::IDS[$key].self::PARAMS;
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException("Failed to download demo image '{$key}' from {$url}: HTTP {$response->status()}");
            }

            file_put_contents($cached, $response->body());
        }

        return $cached;
    }

    /**
     * Generates a flat-color placeholder brand mark (rounded rect + white
     * initials/short label) — used for Partner logos instead of hot-linking
     * a real organization's trademarked logo.
     */
    public function logo(string $slug, string $label, string $hex): string
    {
        $cached = "{$this->cacheDir}/logo-{$slug}.png";

        if (! file_exists($cached)) {
            $this->generateLogo($cached, $label, $hex);
        }

        return $cached;
    }

    private function generateLogo(string $path, string $label, string $hex): void
    {
        $width = 480;
        $height = 200;

        $im = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);

        [$r, $g, $b] = array_values($this->hexToRgb($hex));
        $bg = imagecolorallocate($im, $r, $g, $b);
        $this->filledRoundedRect($im, 0, 0, $width - 1, $height - 1, 24, $bg);

        $white = imagecolorallocate($im, 255, 255, 255);
        $font = 5; // GD built-in font, widest available without a TTF file
        $textWidth = imagefontwidth($font) * strlen($label);
        $x = intdiv($width - $textWidth, 2);
        $y = intdiv($height - imagefontheight($font), 2);
        imagestring($im, $font, max($x, 12), $y, $label, $white);

        imagepng($im, $path);
        imagedestroy($im);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function filledRoundedRect($im, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * A minimal, valid, single-page PDF — hand-built at the PDF object
     * level (no dependency needed for what's just a placeholder document).
     */
    public function pdf(string $slug, string $title, array $lines = []): string
    {
        $cached = "{$this->cacheDir}/pdf-{$slug}.pdf";

        if (! file_exists($cached)) {
            file_put_contents($cached, $this->buildPdf($title, $lines));
        }

        return $cached;
    }

    private function buildPdf(string $title, array $lines): string
    {
        $stream = "BT /F1 20 Tf 50 760 Td ({$this->pdfEscape($title)}) Tj ET\n";
        $stream .= "BT /F1 11 Tf 50 720 Td\n";

        foreach ($lines as $line) {
            $stream .= "({$this->pdfEscape($line)}) Tj 0 -18 Td\n";
        }

        $stream .= "ET\n";
        $streamLength = strlen($stream);

        $objects = [];
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 5 0 R >> >> /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
        $objects[4] = "4 0 obj\n<< /Length {$streamLength} >>\nstream\n{$stream}endstream\nendobj\n";
        $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $body;
        }

        $xrefStart = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
