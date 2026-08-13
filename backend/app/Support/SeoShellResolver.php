<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Department;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\GalleryAlbum;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Audit fix (High remediation) — the SRS is explicit and binding here:
 * "Server-rendered meta tags per page." Every entity already embeds its
 * own `seo` object on its public detail API response (see
 * SeoPublicEmbeddingTest) and useSeoHead.ts already applies the same
 * fallback precedence client-side — this resolver is that exact same
 * precedence (seo field, then the entity's own natural title/excerpt,
 * then Settings' seo_defaults group), just run in PHP against the path
 * a crawler actually requested, so the *first* response byte already
 * has real tags instead of relying on JS execution.
 *
 * Deliberately mirrors AppRoutes.tsx's public route table (courses/:slug,
 * blog/:slug, etc.) rather than driving off it programmatically — this
 * is plain Eloquent path matching, no shared route-definition file
 * between the two projects exists or is worth building for eight
 * patterns.
 */
class SeoShellResolver
{
    private const SITE_NAME = 'PNK Global Campus';

    /** @return array{title:string,description:?string,canonical:string,keywords:?string,ogTitle:string,ogDescription:?string,ogImage:?string,twitterTitle:string,twitterDescription:?string,twitterImage:?string,robotsIndex:bool,robotsFollow:bool,notFound:bool} */
    public function resolve(string $path): array
    {
        $siteUrl = rtrim((string) (Setting::where('key', 'site_url')->value('value') ?: config('app.url')), '/');
        $canonical = $siteUrl.'/'.ltrim($path, '/');
        $trimmed = trim($path, '/');

        // /admin/* is authenticated tooling, not indexed content (see
        // this class's docblock) — a generic, accurate title rather than
        // running it through entity/static-path resolution, where it
        // would otherwise fall through to a misleading "Page Not Found".
        if ($trimmed === 'admin' || str_starts_with($trimmed, 'admin/')) {
            $meta = $this->buildMeta('Admin', null, null, $canonical);

            return [...$meta, 'robotsIndex' => false, 'robotsFollow' => false, 'notFound' => false];
        }

        $resolved = $this->resolveEntity($path) ?? $this->resolveStatic($path);
        [$title, $description, $seo, $notFound] = $resolved;
        $meta = $this->buildMeta($title, $description, $seo, $canonical);

        if ($notFound) {
            $meta['robotsIndex'] = false;
            $meta['robotsFollow'] = false;
        }

        return [...$meta, 'notFound' => $notFound];
    }

    /**
     * Only returns null for a two-segment prefix/slug combination this
     * resolver doesn't recognize at all (e.g. `/admin/settings`) — a
     * *recognized* prefix with no matching row (a real 404, deleted or
     * mistyped slug) still returns a result, just one flagged notFound.
     *
     * @return array{0:string,1:?string,2:?SeoMeta,3:bool}|null
     */
    private function resolveEntity(string $path): ?array
    {
        $segments = explode('/', trim($path, '/'));

        if (count($segments) === 2) {
            [$prefix, $slug] = $segments;

            $entity = match ($prefix) {
                'courses' => Course::published()->with('seoMeta')->where('slug', $slug)->first(),
                'blog' => BlogPost::published()->with('seoMeta')->where('slug', $slug)->first(),
                'news' => News::published()->with('seoMeta')->where('slug', $slug)->first(),
                'events' => Event::published()->with('seoMeta')->where('slug', $slug)->first(),
                'faculties' => Faculty::published()->with('seoMeta')->where('slug', $slug)->first(),
                'departments' => Department::published()->with('seoMeta')->where('slug', $slug)->first(),
                // Audit fix (High remediation) — this arm was missing
                // entirely, so every /gallery/{slug} path fell through to
                // resolveStatic() (which doesn't recognize it either) and
                // returned a 404/noindex SSR shell for a page that renders
                // fine for real visitors — invisible to search engines and
                // social unfurlers. GalleryAlbum has no seoMeta relation
                // (unlike the other entities here), hence no ->with() and
                // no `2` (SeoMeta) element below.
                'gallery' => GalleryAlbum::active()->where('slug', $slug)->first(),
                default => 'unrecognized',
            };

            if ($entity === 'unrecognized') {
                return null;
            }

            if (! $entity) {
                return $this->notFoundResult();
            }

            $title = match (true) {
                $entity instanceof Course => $entity->course_name,
                $entity instanceof Faculty, $entity instanceof Department => $entity->name,
                default => $entity->title,
            };

            $description = match (true) {
                $entity instanceof Course => $entity->overview,
                $entity instanceof BlogPost, $entity instanceof News => $entity->excerpt,
                // Audit fix (Medium remediation) — Event has no plain-text
                // excerpt field like BlogPost/News do, only a rich-text
                // `description`; used as-is, its HTML tags showed up
                // literally in the rendered <meta name="description">.
                $entity instanceof Event => $entity->description ? Str::limit(strip_tags($entity->description), 155) : null,
                $entity instanceof GalleryAlbum => $entity->description,
                default => null,
            };

            // GalleryAlbum has no seoMeta relation (see the match above).
            $seo = $entity instanceof GalleryAlbum ? null : $entity->seoMeta;

            return [$title, $description, $seo, false];
        }

        if (count($segments) === 1 && $segments[0] !== '' && ! $this->isKnownStaticPath('/'.$segments[0])) {
            $page = Page::publiclyVisible()->with('seoMeta')->where('slug', $segments[0])->first();

            return $page ? [$page->title, null, $page->seoMeta, false] : $this->notFoundResult();
        }

        return null;
    }

    /** @return array{0:string,1:?string,2:null,3:bool} */
    private function resolveStatic(string $path): array
    {
        $key = '/'.trim($path, '/');

        if (! $this->isKnownStaticPath($key)) {
            return $this->notFoundResult();
        }

        return [self::STATIC_TITLES[$key], null, null, false];
    }

    /** @return array{0:string,1:null,2:null,3:bool} */
    private function notFoundResult(): array
    {
        return ['Page Not Found', null, null, true];
    }

    private function isKnownStaticPath(string $path): bool
    {
        return array_key_exists($path, self::STATIC_TITLES);
    }

    /** Matches GenerateSitemap::STATIC_PATHS' path list (plus '/' for home). */
    private const STATIC_TITLES = [
        '/' => 'Home',
        '/faculties' => 'Faculties',
        '/departments' => 'Departments',
        '/courses' => 'Courses',
        '/blog' => 'Blog',
        '/news' => 'News',
        '/events' => 'Events',
        '/gallery' => 'Gallery',
        '/faq' => 'Frequently Asked Questions',
        '/downloads' => 'Downloads',
        '/contact' => 'Contact Us',
        '/apply' => 'Apply Now',
        '/search' => 'Search',
    ];

    /** @return array{title:string,description:?string,canonical:string,keywords:?string,ogTitle:string,ogDescription:?string,ogImage:?string,twitterTitle:string,twitterDescription:?string,twitterImage:?string,robotsIndex:bool,robotsFollow:bool} */
    private function buildMeta(string $naturalTitle, ?string $naturalDescription, ?SeoMeta $seo, string $canonical): array
    {
        $defaults = $this->seoDefaults();

        $title = $seo?->seo_title ?: "{$naturalTitle} | ".self::SITE_NAME;
        $description = $seo?->meta_description ?: $naturalDescription ?: $defaults['default_meta_description'];

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $seo?->canonical_url ?: $canonical,
            'keywords' => $seo?->keywords ?: $defaults['default_keywords'],
            'ogTitle' => $seo?->og_title ?: $title,
            'ogDescription' => $seo?->og_description ?: $description,
            'ogImage' => $this->resolveImage($seo?->og_image_media_id ?: $defaults['default_og_image_media_id']),
            'twitterTitle' => $seo?->twitter_title ?: $title,
            'twitterDescription' => $seo?->twitter_description ?: $description,
            'twitterImage' => $this->resolveImage($seo?->twitter_image_media_id) ?: $this->resolveImage($defaults['default_og_image_media_id']),
            'robotsIndex' => $seo?->robots_index ?? true,
            'robotsFollow' => $seo?->robots_follow ?? true,
        ];
    }

    /** @return array{default_meta_description:?string,default_keywords:?string,default_og_image_media_id:?int} */
    private function seoDefaults(): array
    {
        $rows = Setting::whereIn('key', ['default_meta_description', 'default_keywords', 'default_og_image_media_id'])
            ->pluck('value', 'key');

        return [
            'default_meta_description' => $rows->get('default_meta_description'),
            'default_keywords' => $rows->get('default_keywords'),
            'default_og_image_media_id' => $rows->get('default_og_image_media_id') ? (int) $rows->get('default_og_image_media_id') : null,
        ];
    }

    private function resolveImage(?int $mediaId): ?string
    {
        if (! $mediaId) {
            return null;
        }

        try {
            return Media::find($mediaId)?->getUrl();
        } catch (\Throwable) {
            return null;
        }
    }
}
