<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Shop\app\Models\Shop;

class ShopController extends Controller
{
    public function shops()
    {
        $shops = Shop::paginate(12);

        return view('templates.default.pages.shops.shops',compact([
            'shops'
        ]));
    }

    public function shop(Shop $shop)
    {
        $ads = $shop->user->ads()->paginate(12);

        return view('templates.default.pages.shops.shop', compact([
            'ads',
            'shop'
        ]));
    }
}
