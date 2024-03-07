<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Modules\Category\app\Models\Category;
use Modules\Core\app\Traits\Files\ImageCompressor;

class UserController extends Controller
{
    use ImageCompressor;

    public function profile()
    {
        $user = auth()->user();

        $allAds = $user->ads()->count();
        $moderatedAds = $user->ads()->whereModeration('moderated')->count();
        $expiredAds = $user->ads()->whereExpired(true)->count();

        $ads = $user->ads()->latest()->limit(3)->get();

        $notifications = $user->notifications()->latest()->limit(5)->get()->toArray();

        $chartData = self::getAdsHistory();

        return view('templates.default.pages.user.profile', compact([
            'allAds',
            'notifications',
            'moderatedAds',
            'expiredAds',
            'chartData',
            'ads'
        ]));
    }

    public function profileAds(User $user)
    {
        $ads = $user->ads()->paginate(12);

        $categories = Category::whereIn('id', $user->ads->pluck('category_id')->toArray())->get();

        return view('templates.default.pages.user.other-ads', compact(
            'ads',
            'categories',
            'user'
        ));
    }

    public function favorites()
    {
        $categories = Category::pluck('title', 'slug');

        return view('templates.default.pages.user.favorites', compact([
            'categories'
        ]));
    }

    public function messages()
    {
        return view('templates.default.pages.user.messages');
    }

    public function balance()
    {
        return view('templates.default.pages.user.balance');
    }

    public function settings(Request $request)
    {
        try {
            $user = auth()->user();

            if ($request->isMethod('POST')) {

                $validated = $request->validate([
                    'avatar' => ['nullable', 'image', 'max:3072'],
                    'first_name' => ['required', 'string', 'max:250'],
                    'last_name' => ['required', 'string', 'max:250'],
                    'email' => ['required', 'string', 'max:250'],
                ]);

                if ($request->hasFile('avatar')) {
                    if ($user->getAttribute('avatar')) {
                        File::delete('/storage/images/' . $user->getAttribute('avatar'));
                    }
                    $validated['avatar'] = self::compressImage($request->file('avatar'), 'AvatarImag');
                }

                $user->update($validated);
            }

            return view('templates.default.pages.user.settings');
        } catch (Exception $exception) {
            return redirect()->route('web:settings');
        }
    }

    private function getAdsHistory()
    {
        $user = auth()->user();

        // Получаем текущую дату
        $currentDate = Carbon::now();

        // Получаем начало и конец текущей недели
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        // Получаем ежедневные объявления за текущую неделю
        $weeklyAds = $user->ads()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });

        // Формируем данные в необходимом формате
        $data = [];

        foreach ($weeklyAds as $day => $ads) {
            $totalAds = $ads->count();

            $data[] = [
                'x' => $day,
                'y' => $totalAds,
                'goals' => [
                    [
                        'name' => trans('All Ads'),
                        'value' => $user->ads()->count(),
                        'strokeHeight' => 3,
                        'strokeColor' => '#Ff5a0a',
                    ],
                    [
                        'name' => trans('Moderated Ads'),
                        'value' => $user->ads()->whereModeration('moderated')->wherePublished(true)->count(),
                        'strokeHeight' => 3,
                        'strokeColor' => '#3DD816',
                    ],
                    [
                        'name' => trans('Expired Ads'),
                        'value' => $user->ads()->whereExpired(true)->count(),
                        'strokeHeight' => 3,
                        'strokeColor' => '#dc3545',
                    ],
                ],
            ];
        }

        return $data;
    }

}
