@php
    $siteSettings = \App\Models\SiteSetting::current();
    $footerPages = \Illuminate\Support\Facades\Schema::hasTable('pages')
        ? \App\Models\Page::query()->inFooter()->get(['slug', 'title'])
        : collect();
@endphp
<footer class="mt-16 border-t border-sand-200 bg-nile-950 text-sand-100">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="font-display text-xl text-gold-400">{{ $siteSettings->displayName() }}</p>
            <p class="mt-4 max-w-xs text-sm leading-6 text-sand-100/75">
                {{ __('store.footer.blurb') }}
            </p>
            @if (filled($siteSettings->support_email) || filled($siteSettings->support_phone) || filled($siteSettings->support_whatsapp))
                <ul class="mt-4 space-y-1 text-sm text-sand-100/80">
                    @if (filled($siteSettings->support_email))
                        <li><a href="mailto:{{ $siteSettings->support_email }}" class="hover:text-white">{{ $siteSettings->support_email }}</a></li>
                    @endif
                    @if (filled($siteSettings->support_phone))
                        <li><a href="tel:{{ $siteSettings->support_phone }}" class="hover:text-white" dir="ltr">{{ $siteSettings->support_phone }}</a></li>
                    @endif
                    @if (filled($siteSettings->support_whatsapp))
                        <li><a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) $siteSettings->support_whatsapp) }}" class="hover:text-white" dir="ltr" target="_blank" rel="noopener">{{ __('store.footer.whatsapp') }}</a></li>
                    @endif
                </ul>
            @endif
            <div class="mt-6">
                <livewire:store.subscribe-form />
            </div>
        </div>
        <div>
            <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.footer.shop') }}</p>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('shop') }}" class="hover:text-white" wire:navigate>{{ __('store.nav.products') }}</a></li>
                <li><a href="{{ route('shop', ['department' => 'books']) }}" class="hover:text-white" wire:navigate>{{ __('store.nav.books') }}</a></li>
                <li><a href="{{ route('shop', ['department' => 'courses']) }}" class="hover:text-white" wire:navigate>{{ __('store.nav.courses') }}</a></li>
                <li><a href="{{ route('shop', ['deals' => 1, 'sort' => 'discount']) }}" class="hover:text-white" wire:navigate>{{ __('store.footer.deals') }}</a></li>
                <li><a href="{{ route('wishlist') }}" class="hover:text-white" wire:navigate>{{ __('store.footer.wishlist') }}</a></li>
                <li><a href="{{ route('cart') }}" class="hover:text-white" wire:navigate>{{ __('store.footer.cart') }}</a></li>
                <li><a href="{{ route('articles') }}" class="hover:text-white" wire:navigate>{{ __('store.content.articles') }}</a></li>
                <li><a href="{{ route('news.index') }}" class="hover:text-white" wire:navigate>{{ __('store.content.news') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.pages.legal') }}</p>
            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($footerPages as $footerPage)
                    <li>
                        <a href="{{ route('pages.show', $footerPage->slug) }}" class="hover:text-white" wire:navigate>{{ $footerPage->title }}</a>
                    </li>
                @empty
                    <li class="text-sand-100/60">{{ __('store.pages.empty') }}</li>
                @endforelse
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.footer.delivery') }}</p>
            <ul class="mt-4 space-y-2 text-sm text-sand-100/80">
                @foreach (__('store.footer.lanes') as $lane)
                    <li>{{ $lane }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-sand-100/60 sm:flex-row sm:items-center sm:justify-between">
            <p>{{ __('store.footer.copy', ['year' => date('Y')]) }}</p>
            <p>{{ __('store.footer.coverage') }}</p>
        </div>
    </div>
</footer>
