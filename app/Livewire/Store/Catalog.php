<?php

namespace App\Livewire\Store;

use App\Enums\ProductKind;
use App\Livewire\Store\Concerns\ManagesBasket;
use App\Models\Product;
use App\Services\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.store')]
class Catalog extends Component
{
    use ManagesBasket;
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'featured')]
    public string $sort = 'featured';

    #[Url(except: '')]
    public string $deals = '';

    #[Url(except: '')]
    public string $department = '';

    public function mount(): void
    {
        if (! in_array($this->department, ['books', 'courses'], true)) {
            $this->department = '';
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'sort', 'deals', 'department'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $title = match (true) {
            $this->q !== '' => __('store.catalog.title_search'),
            $this->department === 'books' => __('store.nav.books'),
            $this->department === 'courses' => __('store.nav.courses'),
            $this->deals === '1' => __('store.catalog.todays_deals'),
            default => __('store.catalog.title_all'),
        };

        return view('livewire.store.catalog', [
            'products' => $this->products(),
            'wishedIds' => app(Wishlist::class)->ids(),
        ])->title($title);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    private function products(): LengthAwarePaginator
    {
        $query = Product::query();
        $term = trim($this->q);
        $kind = match ($this->department) {
            'books' => ProductKind::Book,
            'courses' => ProductKind::Course,
            default => null,
        };

        if ($kind instanceof ProductKind) {
            $query->where('kind', $kind);
        } elseif ($this->deals !== '1') {
            $query->where('kind', ProductKind::Product);
        }

        if ($this->deals === '1') {
            $query->where('is_deal', true);
        }

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder->where('name->en', 'like', $like)
                    ->orWhere('name->ar', 'like', $like)
                    ->orWhere('brand->en', 'like', $like)
                    ->orWhere('brand->ar', 'like', $like)
                    ->orWhere('description->en', 'like', $like)
                    ->orWhere('description->ar', 'like', $like);
            });
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating'),
            'discount' => $query->orderByRaw('CASE WHEN compare_price IS NULL THEN 0 ELSE (compare_price - price) END DESC'),
            default => $query->orderByDesc('is_featured')->orderByDesc('rating'),
        };

        return $query->paginate(12);
    }
}
