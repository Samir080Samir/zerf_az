<?php

use App\Http\Controllers\Web\Templates\Default\AdController;
use App\Http\Controllers\Web\Templates\Default\CategoryController;
use App\Http\Controllers\Web\Templates\Default\DefaultController;
use App\Http\Controllers\Web\Templates\Default\FavoriteController;
use App\Http\Controllers\Web\Templates\Default\PaymentController;
use App\Http\Controllers\Web\Templates\Default\ShopController;
use App\Http\Controllers\Web\Templates\Default\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Core\app\Http\Middleware\PrefixLocale;
use Modules\Core\app\Http\Middleware\SiteOffline;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider, and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth
Route::withoutMiddleware(SiteOffline::class)->group(function () {
    Auth::routes();
});

Route::name('web:')->prefix(PrefixLocale::getLocale())->group(function () {

    // Global
    Route::controller(DefaultController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/change-locale', 'chanceLocale')->name('change-locale');
    });

    //Favorites
    Route::controller(FavoriteController::class)->middleware('auth')->prefix('favorites')->group(function () {
        Route::post('/ajax', 'indexAjax')->name('favorites.ajax');
        Route::post('/toggle', 'toggle')->name('favorites.toggle');
        Route::delete('/clear', 'clear')->name('favorites.clear');
        Route::post('/remove/selected', 'removeFavoriteSelected')->name('favorites.remove.selected');
        Route::post('/add/selected', 'addToCartFavoriteSelected')->name('favorites.add.selected');
    });

    // User
    Route::controller(UserController::class)->middleware('auth')->prefix('user')->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::get('/other-ads/{user}', 'profileAds')->name('other-ads:user')->withoutMiddleware('auth');
        Route::get('/favorites', 'favorites')->name('favorites');
        Route::get('/messages', 'messages')->name('messages');
        Route::get('/balance', 'balance')->name('balance');
        Route::match(['get', 'post'], '/settings', 'settings')->name('settings');
    });

    Route::controller(AdController::class)->group(function () {
        Route::resource('ads', AdController::class)->except('show');
        Route::post('/ads/ajax', 'indexAjax')->name('ads.ajax');
    });

    Route::controller(ShopController::class)->prefix('shops')->group(function () {
        Route::get('/', 'shops')->name('shops.index');
        Route::get('/{shop:slug}', 'shop')->name('shops.shop:slug');
    });

    // Category, category filters, or ads
    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('/', 'categories')->name('categories');

        Route::post('/filters', 'categoryFilters')->name('category.filters');

        Route::get('/{category?}/{ad?}', 'category')
            ->where('category', '^[a-zA-Z0-9-_\/]+$')
            ->where('ad', '[a-zA-Z0-9-_\/]+')
            ->name('categories.ads.category:slug.ad:id');
    });

    Route::post('/payment/create', [PaymentController::class,'pay'])->name('payment.create');
    Route::post('/payment/add-balance', [PaymentController::class,'addBalance'])->name('payment.add-balance');

    Route::view('/faq','templates.default.pages.static.faq')->name('faq');
    Route::view('/about','templates.default.pages.static.about')->name('about');
    Route::view('/contact','templates.default.pages.static.contact')->name('contact');
    Route::view('/terms-of-use','templates.default.pages.static.terms-of-use')->name('terms-of-use');
    Route::view('/privacy-policy','templates.default.pages.static.privacy-policy')->name('privacy-policy');
});
