<?php

namespace App\Livewire\Store;

use App\Enums\ProductKind;
use App\Livewire\Store\Concerns\ManagesBasket;
use App\Models\Article;
use App\Models\Product;
use App\Models\Testimonial;
use App\Services\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class Home extends Component
{
    use ManagesBasket;

    public function render(): View
    {
        return view('livewire.store.home', [
            'deals' => Product::query()
                ->where('is_deal', true)
                ->orderByRaw('CASE WHEN compare_price IS NULL THEN 0 ELSE (compare_price - price) END DESC')
                ->take(8)
                ->get(),
            'products' => Product::query()->where('kind', ProductKind::Product)->orderByDesc('is_featured')->orderByDesc('rating')->take(8)->get(),
            'books' => Product::query()->where('kind', ProductKind::Book)->orderByDesc('rating')->take(6)->get(),
            'courses' => Product::query()->where('kind', ProductKind::Course)->orderByDesc('rating')->take(6)->get(),
            'wishedIds' => app(Wishlist::class)->ids(),
            'testimonials' => Schema::hasTable('testimonials')
                ? Testimonial::query()->published()->orderBy('sort')->take(4)->get()
                : collect(),
            'articles' => Schema::hasTable('articles')
                ? Article::query()->published()->orderByDesc('published_at')->orderByDesc('id')->take(3)->get()
                : collect(),
        ])->title(__('store.home.meta_title'));
    }
}
