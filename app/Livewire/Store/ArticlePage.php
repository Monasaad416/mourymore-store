<?php

namespace App\Livewire\Store;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class ArticlePage extends Component
{
    public Article $article;

    public function mount(string $slug): void
    {
        abort_unless(Schema::hasTable('articles'), 404);

        $this->article = Article::query()->published()->where('slug', $slug)->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.store.article-page')
            ->title($this->article->displayMetaTitle())
            ->layoutData([
                'description' => $this->article->displayMetaDescription(),
            ]);
    }
}
