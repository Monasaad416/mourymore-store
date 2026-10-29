<?php

namespace App\Livewire\Store;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class CmsPage extends Component
{
    public Page $page;

    public function mount(string $slug): void
    {
        abort_unless(Schema::hasTable('pages'), 404);

        $this->page = Page::query()->published()->where('slug', $slug)->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.store.cms-page')
            ->title($this->page->displayMetaTitle())
            ->layoutData([
                'description' => $this->page->displayMetaDescription(),
            ]);
    }
}
