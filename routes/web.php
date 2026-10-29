<?php

use App\Http\Controllers\DigitalDownloadController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Store\ArticlePage;
use App\Livewire\Store\ArticlesPage;
use App\Livewire\Store\CartPage;
use App\Livewire\Store\Catalog;
use App\Livewire\Store\CheckoutPage;
use App\Livewire\Store\CmsPage;
use App\Livewire\Store\Home;
use App\Livewire\Store\NewsItemPage;
use App\Livewire\Store\NewsPage;
use App\Livewire\Store\OrderPage;
use App\Livewire\Store\ProductPage;
use App\Livewire\Store\WishlistPage;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back(fallback: route('home'));
})->name('locale');

Route::get('/', Home::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/articles', ArticlesPage::class)->name('articles');
Route::get('/articles/{slug}', ArticlePage::class)->name('articles.show');
Route::get('/news', NewsPage::class)->name('news.index');
Route::get('/news/{slug}', NewsItemPage::class)->name('news.show');
Route::get('/pages/{slug}', CmsPage::class)->name('pages.show');
Route::get('/shop', Catalog::class)->name('shop');
Route::get('/c/{category:slug}', function () {
    return redirect()->route('shop');
})->name('category');
Route::get('/p/{product:slug}', ProductPage::class)->name('product');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/wishlist', WishlistPage::class)->name('wishlist');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/orders/{order:number}', OrderPage::class)->name('orders.show');
Route::get('/downloads/{orderItem}', DigitalDownloadController::class)->middleware('signed')->name('downloads.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
