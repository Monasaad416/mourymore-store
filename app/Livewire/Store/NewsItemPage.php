<?php

namespace App\Livewire\Store;

use App\Models\NewsItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class NewsItemPage extends Component
{
    public NewsItem $item;

    public function mount(string $slug): void
    {
        abort_unless(Schema::hasTable('news'), 404);

        $this->item = NewsItem::query()->published()->where('slug', $slug)->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.store.news-item-page')
            ->title($this->item->displayTitle())
            ->layoutData([
                'description' => $this->item->displayExcerpt(),
            ]);
    }
}
