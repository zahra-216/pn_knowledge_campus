<?php

namespace App\Http\Controllers\Api\V1\Menus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menus\MenuItemReorderRequest;
use App\Http\Requests\Menus\MenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Support\ApiResponse;
use App\Support\PublicCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * API Design, Section 8.3 — /admin/menus/{menu}/items sub-resource.
 */
class MenuItemController extends Controller
{
    public function index(Menu $menu): JsonResponse
    {
        Gate::authorize('viewAny', MenuItem::class);

        $menu->load('topLevelItems.children.children');

        return ApiResponse::success(MenuItemResource::collection($menu->topLevelItems));
    }

    public function store(MenuItemRequest $request, Menu $menu): JsonResponse
    {
        Gate::authorize('update', MenuItem::class);

        $item = $menu->items()->create($request->validated());

        PublicCache::forgetMenu($menu->name);

        return ApiResponse::success(new MenuItemResource($item), 201);
    }

    public function update(MenuItemRequest $request, Menu $menu, MenuItem $item): JsonResponse
    {
        Gate::authorize('update', MenuItem::class);

        $item->update($request->validated());

        PublicCache::forgetMenu($menu->name);

        return ApiResponse::success(new MenuItemResource($item));
    }

    public function destroy(Menu $menu, MenuItem $item): Response
    {
        Gate::authorize('update', MenuItem::class);

        $item->delete();

        PublicCache::forgetMenu($menu->name);

        return response()->noContent();
    }

    /**
     * PATCH /api/v1/admin/menus/{menu}/items/reorder — bulk order/nesting
     * update, applied in one transaction so a partial failure never
     * leaves the tree half-reordered.
     */
    public function reorder(MenuItemReorderRequest $request, Menu $menu): JsonResponse
    {
        Gate::authorize('update', MenuItem::class);

        DB::transaction(function () use ($request, $menu) {
            foreach ($request->input('items') as $entry) {
                $menu->items()->whereKey($entry['id'])->update([
                    'parent_id' => $entry['parent_id'] ?? null,
                    'order' => $entry['order'],
                ]);
            }
        });

        PublicCache::forgetMenu($menu->name);

        $menu->load('topLevelItems.children.children');

        return ApiResponse::success(MenuItemResource::collection($menu->topLevelItems));
    }
}
