<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Modules\Ad\app\Models\Ad;
use Modules\Category\app\Models\Category;
use Modules\City\app\Models\City;
use Modules\Core\app\Helpers\CoreHelper;

class DefaultController extends Controller
{
    public function index()
    {
        $freeAds = Ad::active()->latest()->take(12)->with('category','city')
            ->orderByDesc('created_at')
            ->orderByDesc('type')->get();

        $vipAds = Ad::activeVip()->latest()->take(12)->with('category','city')
            ->orderByDesc('created_at')
            ->orderByDesc('type')->get();

        $cities = City::inRandomOrder()->take(8)->withCount('ads')->get();

        $adCount = CoreHelper::formatLargeNumber(Ad::active()->count());
        $cityCount = CoreHelper::formatLargeNumber(City::count());
        $userCount = CoreHelper::formatLargeNumber(User::count());
        $userStoreCount = CoreHelper::formatLargeNumber(User::has('shop')->count());

        return view('templates.default.home.index',compact([
            'vipAds',
            'freeAds',
            'cities',
            'adCount',
            'cityCount',
            'userCount',
            'userStoreCount'
        ]));
    }

    public function chanceLocale()
    {
        try {
            return CoreHelper::changeLocale();
        } catch (Exception $e) {
            return  $e->getMessage();
        }
    }
}
