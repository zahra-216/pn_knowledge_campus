<?php

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.4 — standalone Tag CRUD. Gated by blog.* for now
 * (see BlogPolicy's docblock — Tag is shared infrastructure with a
 * future News module).
 */
class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Tag::class);

        $tags = Tag::query()
            ->withCount('blogPosts')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return ApiResponse::success(TagResource::collection($tags));
    }

    public function store(TagRequest $request): JsonResponse
    {
        Gate::authorize('create', Tag::class);

        $tag = Tag::create($request->validated());

        return ApiResponse::success(new TagResource($tag), 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        Gate::authorize('viewAny', Tag::class);

        return ApiResponse::success(new TagResource($tag->loadCount('blogPosts')));
    }

    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
        Gate::authorize('update', Tag::class);

        $tag->update($request->validated());

        return ApiResponse::success(new TagResource($tag));
    }

    public function destroy(Tag $tag): Response
    {
        Gate::authorize('delete', Tag::class);

        $tag->delete();

        return response()->noContent();
    }
}
