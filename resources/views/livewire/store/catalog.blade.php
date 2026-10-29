<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-nile-700 uppercase">{{ __('store.catalog.department') }}</p>
            <h1 class="font-display mt-1 text-4xl">
                @if ($q !== '')
                    {{ __('store.catalog.results', ['query' => $q]) }}
                @elseif ($department === 'books')
                    {{ __('store.nav.books') }}
                @elseif ($department === 'courses')
                    {{ __('store.nav.courses') }}
                @elseif ($deals === '1')
                    {{ __('store.catalog.todays_deals') }}
                @else
                    {{ __('store.catalog.all') }}
                @endif
            </h1>
        </div>
        <p class="text-sm text-ink/50">{{ __('store.catalog.items', ['count' => $products->total()]) }}</p>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside class="space-y-6">
            <form action="{{ route('shop') }}" method="get" class="lg:hidden">
                @if ($department !== '')
                    <input type="hidden" name="department" value="{{ $department }}">
                @endif
                @if ($deals === '1')
                    <input type="hidden" name="deals" value="1">
                @endif
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('store.catalog.search_aisle') }}" class="h-11 w-full rounded-md bg-white px-4 text-sm ring-1 ring-black/10 outline-none">
            </form>

            <div>
                <p class="text-xs font-semibold text-ink/45 uppercase">{{ __('store.catalog.departments') }}</p>
                <div class="mt-3 flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible">
                    <a href="{{ route('shop') }}" class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ $department === '' && $deals !== '1' ? 'bg-nile-900 text-white' : 'bg-white ring-1 ring-black/5' }}" wire:navigate>{{ __('store.nav.products') }}</a>
                    <a href="{{ route('shop', ['department' => 'books']) }}" class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ $department === 'books' ? 'bg-nile-900 text-white' : 'bg-white ring-1 ring-black/5' }}" wire:navigate>{{ __('store.nav.books') }}</a>
                    <a href="{{ route('shop', ['department' => 'courses']) }}" class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ $department === 'courses' ? 'bg-nile-900 text-white' : 'bg-white ring-1 ring-black/5' }}" wire:navigate>{{ __('store.nav.courses') }}</a>
                    <a href="{{ route('shop', ['deals' => 1]) }}" class="shrink-0 rounded-md px-3 py-1.5 text-sm {{ $deals === '1' ? 'bg-deal text-white' : 'bg-white ring-1 ring-black/5' }}" wire:navigate>{{ __('store.catalog.deals') }}</a>
                </div>
            </div>

            <label class="block">
                <span class="text-xs font-semibold text-ink/45 uppercase">{{ __('store.catalog.sort') }}</span>
                <select wire:model.live="sort" class="mt-2 h-11 w-full rounded-md bg-white px-3 text-sm ring-1 ring-black/10 outline-none">
                    <option value="featured">{{ __('store.catalog.sorts.featured') }}</option>
                    <option value="discount">{{ __('store.catalog.sorts.discount') }}</option>
                    <option value="price_asc">{{ __('store.catalog.sorts.price_asc') }}</option>
                    <option value="price_desc">{{ __('store.catalog.sorts.price_desc') }}</option>
                    <option value="rating">{{ __('store.catalog.sorts.rating') }}</option>
                </select>
            </label>
        </aside>

        <div>
            <form class="mb-4 hidden lg:block" action="{{ route('shop') }}" method="get">
                @if ($department !== '')
                    <input type="hidden" name="department" value="{{ $department }}">
                @endif
                @if ($deals === '1')
                    <input type="hidden" name="deals" value="1">
                @endif
                @if ($sort !== 'featured')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('store.nav.search_placeholder') }}" class="h-12 w-full rounded-md bg-white px-5 text-sm ring-1 ring-black/10 outline-none">
            </form>

            @if ($products->isEmpty())
                <div class="rounded-md bg-white px-6 py-16 text-center ring-1 ring-black/5">
                    <p class="font-display text-3xl">{{ __('store.catalog.empty_title') }}</p>
                    <p class="mt-2 text-sm text-ink/60">{{ __('store.catalog.empty_text') }}</p>
                    <a href="{{ route('shop') }}" class="mt-6 inline-flex rounded-md bg-nile-900 px-5 py-2.5 text-sm font-semibold text-white" wire:navigate>{{ __('store.catalog.clear') }}</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <x-store.product-card :product="$product" :wished="in_array($product->id, $wishedIds, true)" wire:key="catalog-{{ $product->id }}" />
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
