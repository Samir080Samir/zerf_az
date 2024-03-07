<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ad\AdStoreRequest;
use App\Http\Requests\Ad\AdUpdateRequest;
use App\Notifications\AdModerate;
use App\Notifications\FillProfileToPublishAd;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Ad\app\Models\Ad;
use Modules\Ad\app\Models\AdGallery;
use Modules\Category\app\Models\Category;
use Modules\City\app\Models\City;
use Modules\Core\app\Helpers\CoreHelper;
use Modules\Core\app\Traits\Files\ImageCompressor;
use Modules\Guest\app\Models\Guest;
use Yajra\DataTables\Facades\DataTables;

class AdController extends Controller
{
    use ImageCompressor;

    public function __construct()
    {
        $this->middleware('permission:store category')->only('index','indexAjax','edit','update','destroy');
    }

    public function index()
    {
        $categories = Category::pluck('title', 'id');

        return view('templates.default.pages.user.ads', compact([
            'categories'
        ]));
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
            ->addColumn('options', function ($q) {
                return view('templates.default.includes.table-fields.ad-options', ['ad' => $q]);
            })->rawColumns(['action'])
            ->make();
    }

    public function create()
    {
        $cities = City::select(['id', 'title'])->pluck('title', 'id');

        return view('templates.default.pages.ads.create', compact([
            'cities',
        ]));
    }

    public function store(AdStoreRequest $request)
    {
        try {

            if (auth()->check() && !$request->user()->getAttribute('name') && !$request->user()->getAttribute('surname') && !$request->user()->getAttribute('email')) {
                $request->user()?->notify(new FillProfileToPublishAd());
                return redirect()->back()->withErrors(trans('Complete your profile edits to post an ad.'));
            }

            $validatedData = $request->safe();


            if($request->filled('first_name') && $request->filled('last_name') && $request->filled('phone') && $request->filled('email')){

                $guest = Guest::create($request->only([
                    'first_name',
                    'last_name',
                    'phone',
                    'email'
                ]));

                $validatedData['guest_id'] = $guest->getAttribute('id');
            }

            $ad = Ad::create($validatedData->except('gallery'));

            $filters = $validatedData['filters'] ?? [];
            $ad->filters()->sync($filters);

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    AdGallery::create([
                        'ad_id' => $ad->id,
                        'image' => self::compressImage($image, 'AdGallery')
                    ]);
                }
            }

            $request->user()?->notify(new AdModerate($ad));

            return response()->json(['redirect_url' => $ad->url()]);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function edit(Ad $ad)
    {
        if ($ad->user->getAttribute('id') !== auth()->id()) {
            return route('web:index');
        }

        $cities = City::select(['id', 'title'])->pluck('title', 'id');

        $imagesPreview = $ad->gallery->map(function ($images) use ($ad) {
            return [
                'id' => $images->getAttribute('id'),
                'src' => $images->image()
            ];
        });

        return view('templates.default.pages.ads.edit', compact([
            'ad',
            'cities',
            'imagesPreview'
        ]));
    }

    public function update(AdUpdateRequest $request, Ad $ad)
    {
        try {
            if ($ad->user->getAttribute('id') !== auth()->id()) {
                return route('web:index');
            }

            $validatedData = $request->safe();

            $ad->update($validatedData->except('gallery'));

            $filters = $validatedData['filters'] ?? [];

            $ad->filters()->sync($filters);

            if (!empty($request->input('preloaded'))) {
                $deleted = array_diff($ad->gallery->pluck('id')->toArray(), $request->input('preloaded'));
                if (!empty($deleted)) {
                    foreach ($deleted as $delete) {
                        $image = AdGallery::find($delete);
                        if ($image) {
                            File::delete('/storage/images/' . $image->getAttribute('image'));
                            $image->delete();
                        }
                    }
                }
            } else {
                foreach ($ad->gallery as $image) {
                    File::delete('/storage/images/' . $image->getAttribute('image'));
                    $image->delete();
                }
            }

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    AdGallery::create([
                        'ad_id' => $ad->getAttribute('id'),
                        'image' => self::compressImage($image, 'AdGallery')
                    ]);
                }
            }

            if ($ad->getAttribute('moderation') !== 'waiting') {
                $request->user()?->notify(new AdModerate($ad));
            }

            return response()->json(['redirect_url' => $ad->url()]);

        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function destroy(Ad $ad)
    {
        try {

            if ($ad->user->getAttribute('id') !== auth()->id()) {
                return route('web:index');
            }

            foreach ($ad->gallery as $image) {
                File::delete('/storage/images/' . $image->getAttribute('image'));
            }

            $ad->delete();


            return response()->json(null, 204);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
        }
    }
}
