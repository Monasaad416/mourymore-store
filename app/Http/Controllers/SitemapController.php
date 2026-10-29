<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController
{
    public function __invoke(): Response
    {
        $urls = collect([
            route('home'),
            route('shop'),
            route('shop', ['department' => 'books']),
            route('shop', ['department' => 'courses']),
            route('articles'),
            route('news.index'),
        ]);

        if (Schema::hasTable('products')) {
            $urls = $urls->merge(
                Product::query()->pluck('slug')->map(fn (string $slug): string => route('product', ['product' => $slug])),
            );
        }

        if (Schema::hasTable('articles')) {
            $urls = $urls->merge(
                Article::query()->published()->pluck('slug')->map(fn (string $slug): string => route('articles.show', $slug)),
            );
        }

        if (Schema::hasTable('news')) {
            $urls = $urls->merge(
                NewsItem::query()->published()->pluck('slug')->map(fn (string $slug): string => route('news.show', $slug)),
            );
        }

        if (Schema::hasTable('pages')) {
            $urls = $urls->merge(
                Page::query()->published()->pluck('slug')->map(fn (string $slug): string => route('pages.show', $slug)),
            );
        }

        return response()
            ->view('sitemap', ['urls' => $urls->unique()->values()])
            ->header('Content-Type', 'application/xml');
    }
}
