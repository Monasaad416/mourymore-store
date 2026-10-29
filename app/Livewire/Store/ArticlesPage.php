<?php

namespace App\Livewire\Store;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class ArticlesPage extends Component
{
    public function render(): View
    {
        abort_unless(Schema::hasTable('articles'), 404);

        return view('livewire.store.articles-page', [
            'articles' => Article::query()->published()->orderByDesc('published_at')->orderByDesc('id')->get(),
        ])->title(__('store.content.articles_title'));
    }
}
