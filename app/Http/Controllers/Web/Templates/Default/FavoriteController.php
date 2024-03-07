<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\app\Helpers\CoreHelper;
use Yajra\DataTables\Facades\DataTables;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return self::unauthorised();
        }

        $adId = $request->input('ad_id');
        $user = $request->user();

        if ($user->favorites->contains($adId)) {
            // Если товар уже в избранном, удаляем его
            $user->favorites()->detach([$adId]);
            $added = false;
        } else {
            // Если товар не в избранном, добавляем его
            $user->favorites()->attach([$adId]);
            $added = true;
        }

        return response()->json([
            'added' => $added,
            'count' => $user->favorites()->count()
        ]);
    }

    public function clear(Request $request)
    {
        $request->user()->favorites()->detach();

        return response()->json(['count' => $request->user()->favorites()->count()]);
    }

    public function removeFavoriteSelected(Request $request)
    {
        if (!auth()->check()) {
            return self::unauthorised();
        }

        $productIds = $request->input('selected-products');

        if (!empty($productIds)) {
            $request->user()->favorites()->detach($productIds);
        }

        return response()->json(['count' => $request->user()->favorites()->count()]);
    }

    public function addToCartFavoriteSelected(Request $request)
    {
        if (!auth()->check()) {
            return self::unauthorised();
        }

        $productIds = $request->input('selected-products');

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $this->addToCart($productId);
            }
        }
    }

    public function indexAjax(Request $request)
    {
        $category = $request->input('category');

        $ads = auth()->user()->ads()->when($category, function ($q) use ($category) {
            return $q->where('category_id', '=', $category);
        });

        return Datatables::of($ads)
            ->addIndexColumn()
            ->editColumn('title', function ($q) {
                return view('templates.default.includes.table-fields.ad-title-image', ['ad' => $q]);
            })
            ->editColumn('status', function ($q) {
                return view('templates.default.includes.table-fields.ad-moderate-status', ['ad' => $q]);
            })
            ->editColumn('moderation', function ($q) {
                return view('templates.default.includes.table-fields.ad-moderate-status', ['ad' => $q]);
            })
            ->editColumn('created_at', function ($q) {
                return CoreHelper::dateFormat($q->getAttribute('created_at'));
            })
            ->editColumn('favorite', function ($q) {
                return view('templates.default.includes.table-fields.ad-favorite', ['ad' => $q]);
            })
            ->make();
    }

    private function unauthorised()
    {
        return response()->json(['success' => false, 'message' => __('Unauthorised.')],401);
    }
}
