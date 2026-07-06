<?php

namespace App\Http\Controllers\Api\V1\SocialLinks;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLinks\SocialLinkRequest;
use App\Http\Resources\SocialLinkResource;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.9 (admin). The public GET /api/v1/social-links
 * endpoint is a deliberate small addition beyond the literal API Design
 * text — see the Milestone 1 implementation notes: the footer (SRS
 * FR-28) needs social links, but the API Design document never lists a
 * public endpoint for them, only for Branches. This mirrors that same
 * Branches pattern.
 */
class SocialLinkController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        $links = SocialLink::orderBy('order')->paginate(20);

        return ApiResponse::success(SocialLinkResource::collection($links));
    }

    public function store(SocialLinkRequest $request): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $link = SocialLink::create($request->validated());

        PublicCache::forgetSocialLinks();

        return ApiResponse::success(new SocialLinkResource($link), 201);
    }

    public function show(SocialLink $social_link): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        return ApiResponse::success(new SocialLinkResource($social_link));
    }

    public function update(SocialLinkRequest $request, SocialLink $social_link): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $social_link->update($request->validated());

        PublicCache::forgetSocialLinks();

        return ApiResponse::success(new SocialLinkResource($social_link));
    }

    public function destroy(SocialLink $social_link): Response
    {
        Gate::authorize('update', Setting::class);

        $social_link->delete();

        PublicCache::forgetSocialLinks();

        return response()->noContent();
    }

    /**
     * GET /api/v1/social-links — public, active links only, ordered.
     * Cached (see PublicCache's docblock).
     */
    public function publicIndex(): JsonResponse
    {
        $data = PublicCache::remember(
            'public.social_links',
            fn () => SocialLinkResource::collection(SocialLink::where('is_active', true)->orderBy('order')->get())->resolve()
        );

        return ApiResponse::success($data);
    }
}
