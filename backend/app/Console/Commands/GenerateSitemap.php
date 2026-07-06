<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\GalleryAlbum;
use App\Models\News;
use App\Models\Page;
use App\Models\Setting;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Milestone 22 (SEO Module) — generates public/sitemap.xml and
 * public/robots.txt from currently published content. Every seoable
 * entity's own `robots_index` flag (set via the per-entity SEO tab,
 * default true) decides whether its URL is included — the same flag
 * useSeoHead already reads client-side for the page's own <meta
 * name="robots"> tag, reused here instead of a second concept.
 *
 * This project is a decoupled frontend/backend (see AGENTS notes on
 * VITE_API_BASE_URL) with no shared origin and no Laravel `web` routing
 * group — so a file written to this app's own public/ would sit on the
 * wrong origin for search engines (the API's domain, not the public
 * site's). Since frontend/ and backend/ are sibling directories in one
 * repo/deploy, the generated files are written directly into
 * frontend/public/ instead, where Vite copies anything verbatim into
 * the built site's own root — resolveOutputDirectory() falls back to
 * this app's own public/ (with a warning) if that sibling directory
 * isn't found, e.g. if this backend is ever deployed standalone.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--path= : Override the output directory (defaults to the sibling frontend/public, or this app\'s own public/ if not found) — mainly for tests}';

    protected $description = 'Generate sitemap.xml and robots.txt from published content (Milestone 22).';

    /** Paths with no per-entity table of their own (see AppRoutes.tsx's public route table). */
    private const STATIC_PATHS = [
        '/' => ['priority' => '1.0', 'changefreq' => 'weekly'],
        '/faculties' => ['priority' => '0.8', 'changefreq' => 'weekly'],
        '/departments' => ['priority' => '0.8', 'changefreq' => 'weekly'],
        '/courses' => ['priority' => '0.9', 'changefreq' => 'weekly'],
        '/blog' => ['priority' => '0.7', 'changefreq' => 'daily'],
        '/news' => ['priority' => '0.7', 'changefreq' => 'daily'],
        '/events' => ['priority' => '0.7', 'changefreq' => 'daily'],
        '/gallery' => ['priority' => '0.5', 'changefreq' => 'weekly'],
        '/faq' => ['priority' => '0.6', 'changefreq' => 'monthly'],
        '/downloads' => ['priority' => '0.6', 'changefreq' => 'monthly'],
        '/contact' => ['priority' => '0.5', 'changefreq' => 'yearly'],
        '/apply' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    ];

    public function handle(): int
    {
        $siteUrl = rtrim((string) (Setting::where('key', 'site_url')->value('value') ?: config('app.url')), '/');

        $urls = collect();

        foreach (self::STATIC_PATHS as $path => $meta) {
            $urls->push(['loc' => $siteUrl.$path, ...$meta]);
        }

        $this->addEntityUrls($urls, $siteUrl, Faculty::published()->get(), fn (Faculty $f) => "/faculties/{$f->slug}", '0.7', 'monthly');
        $this->addEntityUrls($urls, $siteUrl, Department::published()->get(), fn (Department $d) => "/departments/{$d->slug}", '0.7', 'monthly');
        $this->addEntityUrls($urls, $siteUrl, Course::published()->get(), fn (Course $c) => "/courses/{$c->slug}", '0.8', 'weekly');
        $this->addEntityUrls($urls, $siteUrl, BlogPost::published()->get(), fn (BlogPost $p) => "/blog/{$p->slug}", '0.6', 'monthly');
        $this->addEntityUrls($urls, $siteUrl, News::published()->get(), fn (News $n) => "/news/{$n->slug}", '0.6', 'monthly');
        $this->addEntityUrls($urls, $siteUrl, Event::published()->get(), fn (Event $e) => "/events/{$e->slug}", '0.6', 'weekly');
        $this->addEntityUrls($urls, $siteUrl, Page::publiclyVisible()->get(), fn (Page $p) => "/{$p->slug}", '0.7', 'monthly');

        // Gallery Albums have no seo_meta row at all (deliberately
        // excluded — see config/seo.php's docblock), so every active
        // album is included unconditionally.
        foreach (GalleryAlbum::active()->get() as $album) {
            $urls->push(['loc' => $siteUrl."/gallery/{$album->slug}", 'priority' => '0.4', 'changefreq' => 'monthly']);
        }

        $outputDir = $this->option('path') ?: $this->resolveOutputDirectory();
        File::put($outputDir.'/sitemap.xml', $this->buildSitemapXml($urls));
        File::put($outputDir.'/robots.txt', $this->buildRobotsTxt($siteUrl));

        $this->info("Wrote sitemap.xml ({$urls->count()} URLs) and robots.txt to {$outputDir}");

        return self::SUCCESS;
    }

    /**
     * @param  EloquentCollection<int, Faculty|Department|Course|BlogPost|News|Event|Page>  $models
     */
    private function addEntityUrls(
        Collection $urls,
        string $siteUrl,
        EloquentCollection $models,
        Closure $pathFor,
        string $priority,
        string $changefreq
    ): void {
        foreach ($models as $model) {
            if (method_exists($model, 'seoMeta') && $model->seoMeta && ! $model->seoMeta->robots_index) {
                continue;
            }

            $urls->push(['loc' => $siteUrl.$pathFor($model), 'priority' => $priority, 'changefreq' => $changefreq]);
        }
    }

    private function buildSitemapXml(Collection $urls): string
    {
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1).'</loc>';
            $xml[] = '    <changefreq>'.$url['changefreq'].'</changefreq>';
            $xml[] = '    <priority>'.$url['priority'].'</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml)."\n";
    }

    private function buildRobotsTxt(string $siteUrl): string
    {
        return implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            '',
            "Sitemap: {$siteUrl}/sitemap.xml",
        ])."\n";
    }

    private function resolveOutputDirectory(): string
    {
        $siblingFrontendPublic = dirname(base_path()).'/frontend/public';

        if (File::isDirectory($siblingFrontendPublic)) {
            return $siblingFrontendPublic;
        }

        $this->warn('frontend/public not found alongside this backend — writing sitemap.xml/robots.txt to this app\'s own public/ instead (wrong origin for a real decoupled deploy; see this command\'s docblock).');

        return public_path();
    }
}
