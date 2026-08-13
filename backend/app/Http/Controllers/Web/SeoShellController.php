<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoShellResolver;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * Audit fix (High remediation) — see SeoShellResolver's docblock for
 * why this exists. Reads the frontend's already-built `dist/index.html`
 * verbatim and republishes it with real `<title>`/meta tags spliced
 * into its `<head>`, then hands off to the exact same compiled SPA
 * bundle to hydrate — the approach the approved Public Website
 * architecture plan called "a thin Laravel-rendered meta shell."
 *
 * Assumes backend and frontend are deployed on the same host/
 * filesystem (sibling `frontend/dist`, or FRONTEND_DIST_PATH) — a
 * deliberate topology decision (see DEPLOYMENT.md's "Frontend" section)
 * made specifically so this route can exist; GenerateSitemap already
 * relies on the same sibling-directory assumption for the same reason
 * (no shared origin otherwise for either file to land on).
 */
class SeoShellController extends Controller
{
    public function show(string $path = ''): Response
    {
        $indexPath = $this->resolveIndexHtmlPath();

        if (! $indexPath) {
            return response(
                "The public site hasn't been built yet. Run `npm run build` in frontend/ and confirm FRONTEND_DIST_PATH — see DEPLOYMENT.md's \"Frontend\" section.",
                503
            );
        }

        $meta = app(SeoShellResolver::class)->resolve($path);
        $html = $this->injectMeta(File::get($indexPath), $meta);

        // A 404 status alongside real HTML (not an error page) — the
        // SPA bundle still loads and React Router's own NotFound.tsx
        // renders the actual page; this only fixes what a crawler sees
        // in the response header before any JS runs.
        return response($html, $meta['notFound'] ? 404 : 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function resolveIndexHtmlPath(): ?string
    {
        $distPath = config('frontend.dist_path');
        $indexPath = rtrim($distPath, '/').'/index.html';

        return File::exists($indexPath) ? $indexPath : null;
    }

    /** @param array{title:string,description:?string,canonical:string,keywords:?string,ogTitle:string,ogDescription:?string,ogImage:?string,twitterTitle:string,twitterDescription:?string,twitterImage:?string,robotsIndex:bool,robotsFollow:bool} $meta */
    private function injectMeta(string $html, array $meta): string
    {
        $tags = [
            '<title>'.e($meta['title']).'</title>',
            $meta['description'] ? '<meta name="description" content="'.e($meta['description']).'">' : null,
            $meta['keywords'] ? '<meta name="keywords" content="'.e($meta['keywords']).'">' : null,
            '<meta name="robots" content="'.($meta['robotsIndex'] ? 'index' : 'noindex').', '.($meta['robotsFollow'] ? 'follow' : 'nofollow').'">',
            '<link rel="canonical" href="'.e($meta['canonical']).'">',
            '<meta property="og:title" content="'.e($meta['ogTitle']).'">',
            $meta['ogDescription'] ? '<meta property="og:description" content="'.e($meta['ogDescription']).'">' : null,
            '<meta property="og:type" content="website">',
            '<meta property="og:url" content="'.e($meta['canonical']).'">',
            $meta['ogImage'] ? '<meta property="og:image" content="'.e($meta['ogImage']).'">' : null,
            '<meta name="twitter:card" content="'.($meta['twitterImage'] ? 'summary_large_image' : 'summary').'">',
            '<meta name="twitter:title" content="'.e($meta['twitterTitle']).'">',
            $meta['twitterDescription'] ? '<meta name="twitter:description" content="'.e($meta['twitterDescription']).'">' : null,
            $meta['twitterImage'] ? '<meta name="twitter:image" content="'.e($meta['twitterImage']).'">' : null,
        ];

        $injected = implode("\n    ", array_filter($tags))."\n  </head>";

        // Vite's built index.html always has a real, single <title> tag
        // (from the source index.html this project ships) — remove it so
        // this route's own <title> above is the one that wins, then
        // splice everything else in right before </head>.
        $html = preg_replace('/<title>.*?<\/title>/s', '', $html, 1);

        return preg_replace('/<\/head>/', $injected, $html, 1);
    }
}
