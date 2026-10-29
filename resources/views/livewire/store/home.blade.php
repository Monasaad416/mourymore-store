<div>
    <section class="mx-auto max-w-7xl px-4 pt-4">
        <div class="grid overflow-hidden rounded-md bg-gold-500 text-ink lg:min-h-[32rem] lg:grid-cols-2">
            <div class="flex flex-col justify-center gap-6 p-7 sm:p-10 lg:p-12">
                <p class="text-xs font-semibold uppercase">{{ __('store.home.kicker') }}</p>
                <h1 class="font-display text-4xl leading-tight text-balance sm:text-5xl">{{ __('store.home.headline') }}</h1>
                <p class="max-w-lg text-base leading-7">{{ __('store.home.lead') }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('shop', ['deals' => 1, 'sort' => 'discount']) }}" class="rounded-md bg-deal px-5 py-3 text-sm font-semibold text-white hover:opacity-90" wire:navigate>{{ __('store.home.cta_deals') }}</a>
                    <a href="{{ route('shop') }}" class="rounded-md bg-nile-900 px-5 py-3 text-sm font-semibold text-white" wire:navigate>{{ __('store.home.cta_all') }}</a>
                </div>
            </div>
            <div class="relative h-72 sm:h-96 lg:h-auto">
                <img src="{{ \App\Models\SiteSetting::current()->coverUrl() }}" alt="{{ \App\Models\SiteSetting::current()->displayName() }}" class="absolute inset-0 h-full w-full object-fit">
            </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <a href="{{ route('shop', ['department' => 'books']) }}" wire:navigate class="overflow-hidden rounded-md bg-white ring-1 ring-black/10 hover:shadow-md">
                <img src="{{ asset('images/departments/books.jpg') }}" alt="" class="aspect-[4/3] w-full object-cover">
                <div class="flex items-center justify-between gap-3 p-4">
                    <p class="font-display text-2xl">{{ __('store.nav.books') }}</p>
                    <span class="shrink-0 rounded-md bg-deal px-3 py-2 text-sm font-semibold text-white">{{ __('store.home.books_cta') }}</span>
                </div>
            </a>
            <a href="{{ route('shop', ['department' => 'courses']) }}" wire:navigate class="overflow-hidden rounded-md bg-white ring-1 ring-black/10 hover:shadow-md">
                <img src="{{ asset('images/departments/courses.jpg') }}" alt="" class="aspect-[4/3] w-full object-cover">
                <div class="flex items-center justify-between gap-3 p-4">
                    <p class="font-display text-2xl">{{ __('store.nav.courses') }}</p>
                    <span class="shrink-0 rounded-md bg-deal px-3 py-2 text-sm font-semibold text-white">{{ __('store.home.courses_cta') }}</span>
                </div>
            </a>
            <a href="{{ route('shop') }}" wire:navigate class="overflow-hidden rounded-md bg-white ring-1 ring-black/10 hover:shadow-md">
                <img src="{{ asset('images/departments/products.jpg') }}" alt="" class="aspect-[4/3] w-full object-cover">
                <div class="flex items-center justify-between gap-3 p-4">
                    <p class="font-display text-2xl">{{ __('store.nav.products') }}</p>
                    <span class="shrink-0 rounded-md bg-nile-900 px-3 py-2 text-sm font-semibold text-white">{{ __('store.home.products_cta') }}</span>
                </div>
            </a>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['key' => 'phones', 'image' => 'images/catalog/iphone.jpg', 'href' => route('shop', ['deals' => 1, 'sort' => 'discount'])],
                ['key' => 'books', 'image' => 'images/catalog/coffee.jpg', 'href' => route('shop', ['department' => 'books'])],
                ['key' => 'courses', 'image' => 'images/catalog/laptop.jpg', 'href' => route('shop', ['department' => 'courses'])],
                ['key' => 'home', 'image' => 'images/catalog/sheets.jpg', 'href' => route('shop')],
            ] as $tile)
                <a href="{{ $tile['href'] }}" wire:navigate class="flex flex-col overflow-hidden rounded-md bg-white ring-1 ring-black/10 hover:shadow-md">
                    <p class="px-4 pt-4 font-display text-xl leading-7">{{ __('store.home.tiles.'.$tile['key'].'.title') }}</p>
                    <img src="{{ asset($tile['image']) }}" alt="" class="mt-3 aspect-square w-full object-cover">
                    <span class="px-4 py-4 text-sm font-semibold text-deal">{{ __('store.home.tiles.'.$tile['key'].'.cta') }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pt-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold text-deal uppercase">{{ __('store.home.this_week') }}</p>
                <h2 class="font-display mt-1 text-3xl">{{ __('store.home.deals_title') }}</h2>
            </div>
            <a href="{{ route('shop', ['deals' => 1, 'sort' => 'discount']) }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.home.all_deals') }}</a>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($deals as $product)
                <x-store.product-card :product="$product" :wished="in_array($product->id, $wishedIds, true)" wire:key="deal-{{ $product->id }}" />
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pt-8">
        <a href="{{ route('shop', ['deals' => 1, 'sort' => 'discount']) }}" wire:navigate class="grid overflow-hidden rounded-md bg-nile-900 text-white lg:min-h-80 lg:grid-cols-2">
            <div class="relative h-64 lg:h-auto">
                <img src="{{ asset('images/catalog/headphones.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            </div>
            <div class="flex flex-col justify-center gap-4 p-7 sm:p-10">
                <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.home.banner.kicker') }}</p>
                <h2 class="font-display text-3xl leading-tight sm:text-4xl">{{ __('store.home.banner.title') }}</h2>
                <p class="max-w-lg text-sm leading-7 text-white/80">{{ __('store.home.banner.text') }}</p>
                <span class="w-fit rounded-md bg-gold-500 px-5 py-3 text-sm font-semibold text-ink">{{ __('store.home.banner.cta') }}</span>
            </div>
        </a>
    </section>

    @if ($books->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-3xl">{{ __('store.nav.books') }}</h2>
                <a href="{{ route('shop', ['department' => 'books']) }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.home.see_all') }}</a>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach ($books as $product)
                    <x-store.product-card :product="$product" :wished="in_array($product->id, $wishedIds, true)" wire:key="book-{{ $product->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($courses->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-3xl">{{ __('store.nav.courses') }}</h2>
                <a href="{{ route('shop', ['department' => 'courses']) }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.home.see_all') }}</a>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach ($courses as $product)
                    <x-store.product-card :product="$product" :wished="in_array($product->id, $wishedIds, true)" wire:key="course-{{ $product->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pt-8">
        <div class="grid gap-3 md:grid-cols-2">
            <a href="{{ route('shop') }}" wire:navigate class="relative block min-h-64 overflow-hidden rounded-md">
                <img src="{{ asset('images/catalog/dates.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="relative flex min-h-64 flex-col justify-end bg-nile-950/55 p-6 text-white">
                    <h2 class="font-display text-3xl">{{ __('store.home.strips.pantry.title') }}</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-white/85">{{ __('store.home.strips.pantry.text') }}</p>
                    <span class="mt-4 w-fit rounded-md bg-deal px-4 py-2 text-sm font-semibold text-white">{{ __('store.home.strips.pantry.cta') }}</span>
                </div>
            </a>
            <a href="{{ route('shop') }}" wire:navigate class="relative block min-h-64 overflow-hidden rounded-md">
                <img src="{{ asset('images/catalog/sneakers.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="relative flex min-h-64 flex-col justify-end bg-nile-950/45 p-6 text-white">
                    <h2 class="font-display text-3xl">{{ __('store.home.strips.style.title') }}</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-white/85">{{ __('store.home.strips.style.text') }}</p>
                    <span class="mt-4 w-fit rounded-md bg-gold-500 px-4 py-2 text-sm font-semibold text-ink">{{ __('store.home.strips.style.cta') }}</span>
                </div>
            </a>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pt-12">
        <div class="flex items-end justify-between gap-4">
            <h2 class="font-display text-3xl">{{ __('store.nav.products') }}</h2>
            <a href="{{ route('shop') }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.home.see_all') }}</a>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-store.product-card :product="$product" :wished="in_array($product->id, $wishedIds, true)" wire:key="goods-{{ $product->id }}" />
            @endforeach
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section
            class="mx-auto max-w-7xl px-4 pt-12"
            x-data="{ i: 0, n: {{ $testimonials->count() }}, timer: null }"
            x-init="if (n > 1) timer = setInterval(() => i = (i + 1) % n, 6000)"
            x-on:mouseenter="clearInterval(timer)"
            x-on:mouseleave="if (n > 1) timer = setInterval(() => i = (i + 1) % n, 6000)"
        >
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-3xl">{{ __('store.content.testimonials') }}</h2>
                @if ($testimonials->count() > 1)
                    <div class="flex gap-2">
                        <button type="button" class="rounded-md bg-nile-900 px-3 py-2 text-sm font-semibold text-white" x-on:click="i = (i - 1 + n) % n">{{ __('store.home.prev') }}</button>
                        <button type="button" class="rounded-md bg-deal px-3 py-2 text-sm font-semibold text-white" x-on:click="i = (i + 1) % n">{{ __('store.home.next') }}</button>
                    </div>
                @endif
            </div>
            <div class="relative mt-5 overflow-hidden rounded-md bg-white ring-1 ring-black/10">
                @foreach ($testimonials as $index => $testimonial)
                    <blockquote
                        wire:key="quote-{{ $testimonial->id }}"
                        x-show="i === {{ $index }}"
                        x-cloak
                        class="px-6 py-8 sm:px-10"
                    >
                        <p class="text-gold-400" aria-hidden="true">{{ str_repeat('★', (int) round($testimonial->rating)) }}</p>
                        <p class="mt-3 max-w-3xl font-display text-2xl leading-9 sm:text-3xl">“{{ $testimonial->displayQuote() }}”</p>
                        <footer class="mt-5 text-sm text-ink/60">
                            <span class="font-semibold text-ink">{{ $testimonial->name }}</span>
                            @if ($testimonial->displayRole())
                                <span> · {{ $testimonial->displayRole() }}</span>
                            @endif
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </section>
    @endif

    @if ($articles->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-3xl">{{ __('store.content.articles_title') }}</h2>
                <a href="{{ route('articles') }}" class="text-sm font-semibold text-nile-800" wire:navigate>{{ __('store.home.see_all') }}</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                @foreach ($articles as $article)
                    <a href="{{ route('articles.show', $article->slug) }}" wire:navigate class="rounded-md bg-white p-5 ring-1 ring-black/10" wire:key="home-article-{{ $article->id }}">
                        <h3 class="font-display text-2xl">{{ $article->displayTitle() }}</h3>
                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-ink/70">{{ $article->displayExcerpt() }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pt-12">
        <div class="grid gap-3 rounded-md bg-white p-4 ring-1 ring-black/10 sm:grid-cols-2 sm:p-6 lg:grid-cols-4">
            @foreach (__('store.home.points') as $point)
                <div class="rounded-md bg-sand-50 p-4">
                    <p class="font-semibold">{{ $point['title'] }}</p>
                    <p class="mt-2 text-sm leading-6 text-ink/65">{{ $point['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
