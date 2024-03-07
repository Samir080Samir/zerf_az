<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ad\app\Models\Ad;
use Modules\Category\app\Models\Category;
use Modules\City\app\Models\City;

class CategoryController extends Controller
{
    public function categories(Request $request)
    {
        $cities = City::select(['slug', 'title'])->pluck('title', 'slug');

        $adsQuery = Ad::active()->with(['city', 'category.parent', 'category.children', 'gallery']);

        $min = $adsQuery->min('price');
        $max = $adsQuery->max('price');

        $ads = $adsQuery->filterPrice($request)->filterSearch($request)->filterCity($request)->orderByDesc('created_at')->paginate(12);

        return view('templates.default.pages.category.categories', compact([
            'cities',
            'ads',
            'min',
            'max'
        ]));
    }

    public function category(Request $request, $category = null, $ad = null)
    {
        $lastSegment = basename($category);

        if (ctype_digit($lastSegment)) {
            $ad = Ad::find($lastSegment);

            if (!$ad) abort(404);

            $ad->increment('views');

            $ads = Ad::with('category.parent', 'category.children')->active()->where('id', '!=', $ad->getAttribute('id'))
                ->where(function ($query) use ($ad) {
                    $query
                        ->orWhere('city_id', '=', $ad->getAttribute('city_id'))
                        ->orWhere('category_id', '=', $ad->getAttribute('category_id'));
                })
                ->limit(36)
                ->orderByDesc('created_at')
                ->get();

            return view('templates.default.pages.ads.show', compact([
                'ad',
                'ads'
            ]));

        } else {
            $category = Category::whereSlug($lastSegment)->first();

            if (!$category) abort(404);
            $cities = City::select(['slug', 'title'])->pluck('title', 'slug');
            $adsQuery = $category->ads()->active()->with(['city', 'category', 'gallery']);

            $min = $adsQuery->min('price');
            $max = $adsQuery->max('price');

            $ads = $adsQuery->filterPrice($request)->filterSearch($request)->filterCity($request)->filters($request)->paginate(12);

            $filters = $category->filtersWithValues->groupBy('type');

            return view('templates.default.pages.category.category', compact([
                'ads',
                'min',
                'max',
                'cities',
                'filters',
                'category',
            ]));
        }
    }

    public function categoryFilters(Request $request)
    {
        $adId = $request->input('ad_id');
        $categoryId = $request->input('category_id');

        $ad = Ad::find($adId);

        $category = Category::find($categoryId);

        $adFilters = $ad?->filters->toArray();

        $filters = $category->filters->groupBy(function ($item) {
            return trim($item->type);
        });

        return $filters->isNotEmpty() ? view('templates.default.includes.filter.category-filters', compact([
            'category',
            'filters',
            'adFilters'
        ]))->render() : null;
    }
}
