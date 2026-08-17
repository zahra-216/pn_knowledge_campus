<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Menu;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Cache;

class BootstrapController extends Controller
{
    public function __invoke()
    {
        $data = Cache::remember('public.bootstrap', 300, function () {
            return [
                'settings' => Setting::pluck('value', 'key'),
                'header_menu' => Menu::where('location', 'header')
                    ->orderBy('order')
                    ->get(['id', 'label', 'url', 'parent_id', 'order']),
                'footer_menu' => Menu::where('location', 'footer')
                    ->orderBy('order')
                    ->get(['id', 'label', 'url', 'parent_id', 'order']),
                'social_links' => SocialLink::orderBy('order')->get(['platform', 'url', 'icon']),
            ];
        });

        return response()->json(['data' => $data]);
    }
}