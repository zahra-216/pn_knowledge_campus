<?php

namespace App\Http\Controllers\Api\V1\Menus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menus\MenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3. SRS Permission Matrix — Menu Builder is Super
 * Admin/Administrator only.
 */
class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Menu::class);

        $menus = Menu::withCount('items')->orderBy('name')->get();

        return ApiResponse::success($menus->map(fn (Menu $menu) => [
            'id' => $menu->id,
            'name' => $menu->name,
            'items_count' => $menu->items_count,
            'created_at' => $menu->created_at,
        ])->all());
    }

    public function store(MenuRequest $request): JsonResponse
    {
        Gate::authorize('update', Menu::class);

        $menu = Menu::create($request->validated());

        return ApiResponse::success(new MenuResource($menu->load('topLevelItems.children.children.children')), 201);
    }

    public function show(Menu $menu): JsonResponse
    {
        Gate::authorize('viewAny', Menu::class);

        return ApiResponse::success(new MenuResource($menu->load('topLevelItems.children.children.children')));
    }

    public function update(MenuRequest $request, Menu $menu): JsonResponse
    {
        Gate::authorize('update', Menu::class);

        $originalName = $menu->name;
        $menu->update($request->validated());

        PublicCache::forgetMenu($originalName);
        PublicCache::forgetMenu($menu->name);

        return ApiResponse::success(new MenuResource($menu->load('topLevelItems.children.children.children')));
    }

    public function destroy(Menu $menu): Response
    {
        Gate::authorize('update', Menu::class);

        $name = $menu->name;
        $menu->delete();

        PublicCache::forgetMenu($name);

        return response()->noContent();
    }

    /**
     * GET /api/v1/menus/{key} — public, unauthenticated. Only active,
     * currently-scheduled items appear; `visible_on` is still included
     * per item rather than filtered server-side, since the browser
     * viewport (not the server) is what actually knows whether to
     * render a desktop- or mobile-only item — matching how the rest of
     * this frontend does responsive layout (CSS breakpoints), not
     * user-agent sniffing.
     *
     * Eager-loading is capped at 3 levels of actual content (top-level,
     * dropdown, mega-menu column) — the schema itself supports unlimited
     * nesting via parent_id, but no real navigation UI (this project's
     * or any other CMS's) reasonably goes deeper than that in practice.
     * The relation chain below goes one level past that (4 deep): each
     * MenuItemResource includes `children` via `whenLoaded('children')`,
     * which omits the key entirely — not `[]` — for any item whose own
     * `children` relation wasn't loaded. Without the 4th level, the
     * deepest real items (mega-menu column entries) would each be
     * missing `children` rather than having an empty array, which broke
     * the admin Menu Builder's tree walker (`nodes is not iterable`)
     * the moment a menu actually reached 3 levels deep.
     *
     * Cached per menu key (see PublicCache's docblock) — invalidated
     * explicitly by every MenuController/MenuItemController write that
     * targets this menu, since a nav bar changing shouldn't have to
     * wait out the TTL.
     */
    public function publicShow(string $key): JsonResponse
    {
        $data = PublicCache::remember("public.menu.{$key}", function () use ($key) {
            $menu = Menu::where('name', $key)->firstOrFail();

            $menu->load(['topLevelItems' => function ($query) {
                $query->where('is_active', true)->currentlyScheduled();
            }, 'topLevelItems.children' => function ($query) {
                $query->where('is_active', true)->currentlyScheduled();
            }, 'topLevelItems.children.children' => function ($query) {
                $query->where('is_active', true)->currentlyScheduled();
            }, 'topLevelItems.children.children.children' => function ($query) {
                $query->where('is_active', true)->currentlyScheduled();
            }]);

            return (new MenuResource($menu))->resolve();
        });

        return ApiResponse::success($data);
    }
}
