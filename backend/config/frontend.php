<?php

/**
 * Audit fix (High remediation) — SeoShellController reads the compiled
 * frontend's index.html from disk to inject server-rendered meta tags
 * into it (see its own docblock). Defaults to the sibling `frontend/dist`
 * directory this monorepo already assumes elsewhere (GenerateSitemap
 * writes sitemap.xml/robots.txt to the sibling frontend/public for the
 * exact same "no shared origin otherwise" reason) — override
 * FRONTEND_DIST_PATH if the build output ever lives somewhere else.
 */
return [
    'dist_path' => env('FRONTEND_DIST_PATH') ?: dirname(base_path()).'/frontend/dist',
];
