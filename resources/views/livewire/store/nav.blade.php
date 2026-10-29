<div>
    <div class="bg-nile-950 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2 text-xs">
            <label class="flex min-w-0 items-center gap-2">
                <svg class="size-3.5 shrink-0 text-gold-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Z" />
                    <circle cx="12" cy="10" r="2.2" />
                </svg>
                <span class="hidden text-gold-400 sm:inline">{{ __('store.nav.deliver_to') }}</span>
                <select wire:model.live="governorate" class="max-w-[9.5rem] truncate bg-transparent font-semibold text-white outline-none sm:max-w-none">
                    @foreach ($governorates as $name)
                        <option value="{{ $name }}" class="text-ink">{{ \App\Support\Shipping::label($name) }}</option>
                    @endforeach
                </select>
            </label>
            <p class="hidden min-w-0 truncate text-white/70 lg:block">{{ __('store.nav.promo', ['amount' => \App\Support\Money::format(\App\Models\SiteSetting::current()->freeShippingOver()), 'code' => \App\Models\Offer::featuredCode()]) }}</p>
            <div class="flex shrink-0 items-center gap-3">
                <nav class="flex items-center gap-1 font-semibold" aria-label="{{ __('store.nav.language') }}">
                    <a href="{{ route('locale', 'ar') }}" class="{{ app()->getLocale() === 'ar' ? 'text-white' : 'text-white/45 hover:text-white' }}">عربي</a>
                    <span class="text-white/25" aria-hidden="true">/</span>
                    <a href="{{ route('locale', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'text-white' : 'text-white/45 hover:text-white' }}">EN</a>
                </nav>
                <a href="{{ route('shop', ['deals' => 1]) }}" class="font-medium text-gold-400 hover:text-white" wire:navigate>{{ __('store.nav.deals') }}</a>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-40 bg-gold-500 text-ink">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2">
            <a href="{{ route('home') }}" class="shrink-0" wire:navigate>
                <img src="{{ \App\Models\SiteSetting::current()->logoUrl() }}" alt="{{ \App\Models\SiteSetting::current()->displayName() }}" class="h-14 w-auto sm:h-16" style="mix-blend-mode:multiply">
            </a>

            <form action="{{ route('shop') }}" method="get" class="hidden min-w-0 flex-1 items-stretch md:flex">
                <label class="sr-only" for="store-department">{{ __('store.nav.department') }}</label>
                <select id="store-department" name="department" class="h-11 rounded-s-md border-0 bg-sand-50 px-3 text-sm text-ink outline-none">
                    <option value="">{{ __('store.nav.products') }}</option>
                    <option value="books">{{ __('store.nav.books') }}</option>
                    <option value="courses">{{ __('store.nav.courses') }}</option>
                </select>
                <label class="sr-only" for="store-search">{{ __('store.nav.search_label') }}</label>
                <input
                    id="store-search"
                    name="q"
                    type="search"
                    placeholder="{{ __('store.nav.search_placeholder') }}"
                    class="h-11 min-w-0 flex-1 border-0 bg-white px-4 text-sm text-ink outline-none placeholder:text-ink/40"
                >
                <button type="submit" class="h-11 rounded-e-md bg-deal px-5 text-sm font-semibold text-white hover:opacity-90">
                    {{ __('store.nav.search') }}
                </button>
            </form>

            <nav class="ms-auto flex items-center gap-1 sm:gap-2">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" class="rounded-sm px-2 py-1.5 text-start hover:ring-1 hover:ring-ink sm:px-3" @click="open = !open">
                        <span class="block text-[10px] text-ink/70">{{ __('store.nav.hello') }}</span>
                        <span class="block text-sm font-semibold leading-4">{{ auth()->check() ? auth()->user()->name : __('store.nav.account') }}</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute end-0 z-50 mt-2 w-56 rounded-md bg-white p-2 text-ink shadow-xl ring-1 ring-black/10">
                        @auth
                            <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-sand-100" wire:navigate>{{ __('store.nav.your_account') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-sand-100" wire:navigate>{{ __('store.nav.sign_in') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-sand-100" wire:navigate>{{ __('store.nav.register') }}</a>
                            @endif
                        @endauth
                        <a href="{{ route('wishlist') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-sand-100" wire:navigate>{{ __('store.nav.wishlist') }}</a>
                        <a href="{{ route('cart') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-sand-100" wire:navigate>{{ __('store.nav.cart') }}</a>
                    </div>
                </div>

                <a href="{{ route('wishlist') }}" class="relative grid size-10 place-items-center rounded-sm hover:ring-1 hover:ring-ink" wire:navigate aria-label="{{ __('store.nav.wishlist') }}">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linejoin="round" d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9Z" />
                    </svg>
                    @if ($wishCount > 0)
                        <span class="absolute top-0 end-0 grid min-w-5 place-items-center rounded-full bg-deal px-1 text-[11px] font-bold text-white">{{ $wishCount }}</span>
                    @endif
                </a>

                <a href="{{ route('cart') }}" class="relative grid size-10 place-items-center rounded-sm hover:ring-1 hover:ring-ink" wire:navigate aria-label="{{ __('store.nav.cart') }}">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" d="M6 7h15l-1.5 9h-12L5 4H2" />
                        <circle cx="9" cy="20" r="1.3" fill="currentColor" stroke="none" />
                        <circle cx="18" cy="20" r="1.3" fill="currentColor" stroke="none" />
                    </svg>
                    @if ($cartCount > 0)
                        <span class="absolute top-0 end-0 grid min-w-5 place-items-center rounded-full bg-deal px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
            </nav>
        </div>

        <form action="{{ route('shop') }}" method="get" class="mx-auto flex max-w-7xl px-4 pb-3 md:hidden">
            <select name="department" class="h-11 rounded-s-md bg-sand-50 px-2 text-sm outline-none" aria-label="{{ __('store.nav.department') }}">
                <option value="">{{ __('store.nav.all') }}</option>
                <option value="books">{{ __('store.nav.books') }}</option>
                <option value="courses">{{ __('store.nav.courses') }}</option>
            </select>
            <input name="q" type="search" placeholder="{{ __('store.nav.search_short') }}" class="h-11 w-full bg-white px-3 text-sm text-ink outline-none">
            <button type="submit" class="h-11 rounded-e-md bg-deal px-4 text-sm font-semibold text-white">{{ __('store.nav.search') }}</button>
        </form>

        <div class="bg-nile-900 text-white">
            <div class="store-scroll mx-auto flex max-w-7xl gap-1 overflow-x-auto px-3 py-2 text-sm">
                <a href="{{ route('shop') }}" class="shrink-0 rounded-sm px-3 py-1 font-medium hover:ring-1 hover:ring-white" wire:navigate>{{ __('store.nav.products') }}</a>
                <a href="{{ route('shop', ['department' => 'books']) }}" class="shrink-0 rounded-sm px-3 py-1 font-medium hover:ring-1 hover:ring-white" wire:navigate>{{ __('store.nav.books') }}</a>
                <a href="{{ route('shop', ['department' => 'courses']) }}" class="shrink-0 rounded-sm px-3 py-1 font-medium hover:ring-1 hover:ring-white" wire:navigate>{{ __('store.nav.courses') }}</a>
                <a href="{{ route('shop', ['deals' => 1, 'sort' => 'discount']) }}" class="shrink-0 rounded-sm px-3 py-1 font-semibold text-gold-400 hover:ring-1 hover:ring-white" wire:navigate>{{ __('store.nav.deals_link') }}</a>
            </div>
        </div>
    </header>
</div>
