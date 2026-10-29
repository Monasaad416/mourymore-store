<div class="mx-auto max-w-7xl px-4 py-8">
    <h1 class="font-display text-4xl">{{ __('store.cart.title') }}</h1>
    <p class="mt-1 text-sm text-ink/55">{{ trans_choice('store.cart.items', $count, ['count' => $count]) }} · {{ __('store.cart.delivering', ['place' => \App\Support\Shipping::label($governorate)]) }}</p>

    @if ($lines->isEmpty())
        <div class="mt-8 rounded-[2rem] bg-white px-6 py-16 text-center ring-1 ring-black/5">
            <p class="font-display text-3xl">{{ __('store.cart.empty_title') }}</p>
            <p class="mt-2 text-sm text-ink/60">{{ __('store.cart.empty_text') }}</p>
            <a href="{{ route('shop') }}" class="mt-6 inline-flex rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-white" wire:navigate>{{ __('store.cart.continue') }}</a>
        </div>
    @else
        @if ($ships && $quote['free_over'] !== null)
            @php
                $freeOver = (float) $quote['free_over'];
                $remaining = max(0, $freeOver - $subtotal);
                $progress = $freeOver > 0 ? min(100, (int) round(($subtotal / $freeOver) * 100)) : 100;
            @endphp
            <div class="mt-6 rounded-2xl bg-white p-4 ring-1 ring-black/5">
                <p class="text-sm font-semibold">
                    @if ($remaining <= 0)
                        {{ __('store.cart.free_unlocked') }}
                    @else
                        {{ __('store.cart.free_progress', ['amount' => \App\Support\Money::format($remaining)]) }}
                    @endif
                </p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-sand-200" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('store.cart.free_progress', ['amount' => \App\Support\Money::format($remaining)]) }}">
                    <div class="h-full rounded-full {{ $remaining <= 0 ? 'bg-nile-900' : 'bg-gold-500' }}" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif
        <div class="mt-6 grid items-start gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-3">
                @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <article class="flex gap-4 rounded-3xl bg-white p-4 ring-1 ring-black/5" wire:key="cart-{{ $product->id }}">
                        <a href="{{ route('product', $product) }}" wire:navigate class="shrink-0">
                            <img src="{{ $product->imageUrl() }}" alt="" class="size-24 rounded-2xl object-cover sm:size-28">
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('product', $product) }}" class="font-semibold hover:text-nile-800" wire:navigate>{{ $product->displayName() }}</a>
                            <p class="mt-1 text-xs text-ink/50">{{ $product->brand }}</p>
                            <p class="mt-2 text-sm font-semibold">{{ \App\Support\Money::format($line['line']) }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <div class="flex items-center rounded-full ring-1 ring-black/10">
                                    <button type="button" wire:click="setQty({{ $product->id }}, {{ $line['qty'] - 1 }})" class="grid size-9 place-items-center" aria-label="{{ __('store.cart.decrease') }}">−</button>
                                    <span class="w-6 text-center text-sm">{{ $line['qty'] }}</span>
                                    <button type="button" wire:click="setQty({{ $product->id }}, {{ $line['qty'] + 1 }})" class="grid size-9 place-items-center" aria-label="{{ __('store.cart.increase') }}">+</button>
                                </div>
                                <button type="button" wire:click="remove({{ $product->id }})" class="text-sm text-ink/55 hover:text-deal">{{ __('store.cart.remove') }}</button>
                                <button type="button" wire:click="saveForLater({{ $product->id }})" class="text-sm font-medium text-nile-800">{{ __('store.cart.save') }}</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="rounded-[2rem] bg-nile-950 p-6 text-white lg:sticky lg:top-28">
                <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.cart.summary') }}</p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-white/70">{{ __('store.cart.subtotal') }}</dt>
                        <dd>{{ \App\Support\Money::format($subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-white/70">{{ __('store.cart.delivery', ['place' => \App\Support\Shipping::label($governorate)]) }}</dt>
                        <dd>{{ $quote['fee'] == 0 ? __('store.cart.free') : \App\Support\Money::format($quote['fee']) }}</dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs leading-5 text-white/55">{{ __('store.cart.free_note', ['eta' => $quote['eta'], 'amount' => \App\Support\Money::format(\App\Models\SiteSetting::current()->freeShippingOver())]) }}</p>
                <div class="mt-4 flex items-end justify-between gap-3 border-t border-white/10 pt-4">
                    <p class="text-sm text-white/70">{{ __('store.cart.estimated') }}</p>
                    <p class="text-2xl font-semibold">{{ \App\Support\Money::format(\App\Models\SiteSetting::current()->addTaxTo($subtotal) + $quote['fee']) }}</p>
                </div>
                <a href="{{ route('checkout') }}" class="mt-5 flex items-center justify-center rounded-full bg-gold-500 py-3 text-sm font-semibold text-white hover:bg-gold-600" wire:navigate>
                    {{ __('store.cart.checkout') }}
                </a>
            </aside>
        </div>
    @endif
</div>
