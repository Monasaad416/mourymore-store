<?php

namespace App\Livewire\Store;

use App\Models\NewsItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class NewsPage extends Component
{
    public function render(): View
    {
        abort_unless(Schema::hasTable('news'), 404);

        return view('livewire.store.news-page', [
            'items' => NewsItem::query()->published()->orderByDesc('published_at')->orderByDesc('id')->get(),
        ])->title(__('store.content.news_title'));
    }
}
