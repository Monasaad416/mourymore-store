<div class="mx-auto max-w-7xl px-4 py-8">
    <p class="text-xs font-semibold text-nile-700 uppercase">{{ __('store.checkout.kicker') }}</p>
    <h1 class="font-display mt-1 text-4xl">{{ __('store.checkout.title') }}</h1>
    <ol class="mt-4 flex flex-wrap gap-3 text-sm text-ink/50">
        <li>{{ __('store.checkout.step_cart') }}</li>
        <li class="font-semibold text-ink">{{ __('store.checkout.step_address') }}</li>
        <li>{{ __('store.checkout.step_done') }}</li>
    </ol>

    @error('cart')
        <p class="mt-4 rounded-2xl bg-deal/10 px-4 py-3 text-sm text-deal">{{ $message }}</p>
    @enderror

    <form wire:submit="placeOrder" class="mt-6 grid items-start gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-4">
            <section class="rounded-[2rem] bg-white p-5 ring-1 ring-black/5 sm:p-6">
                <h2 class="font-semibold">{{ __('store.checkout.address') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.full_name') }}</span>
                        <input wire:model="name" type="text" autocomplete="name" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                        @error('name') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.mobile') }}</span>
                        <input wire:model="phone" type="tel" inputmode="numeric" placeholder="01012345678" autocomplete="tel" dir="ltr" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                        @error('phone') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.email') }} <span class="text-ink/35">({{ __('store.checkout.optional') }})</span></span>
                        <input wire:model="email" type="email" autocomplete="email" dir="ltr" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                        @error('email') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.governorate') }}</span>
                        <select wire:model.live="governorate" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none">
                            @foreach ($governorates as $governorateName)
                                <option value="{{ $governorateName }}">{{ \App\Support\Shipping::label($governorateName) }}</option>
                            @endforeach
                        </select>
                        @error('governorate') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.city') }}</span>
                        <input wire:model="city" type="text" placeholder="{{ __('store.checkout.city_placeholder') }}" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                        @error('city') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.address_label') }}</span>
                        <input wire:model="address" type="text" placeholder="{{ __('store.checkout.address_placeholder') }}" autocomplete="street-address" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                        @error('address') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.notes') }} <span class="text-ink/35">({{ __('store.checkout.optional') }})</span></span>
                        <input wire:model="notes" type="text" placeholder="{{ __('store.checkout.notes_placeholder') }}" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none">
                    </label>
                </div>
            </section>

            <section class="rounded-[2rem] bg-white p-5 ring-1 ring-black/5 sm:p-6">
                <h2 class="font-semibold">{{ __('store.checkout.payment') }}</h2>
                <p class="mt-1 text-sm text-ink/55">{{ __('store.checkout.payment_lead') }}</p>
                <div class="mt-4 grid gap-2">
                    @foreach ($payments as $key => $method)
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl px-4 py-3 ring-1 {{ $payment === $key ? 'bg-nile-50 ring-nile-700' : 'ring-black/10 hover:bg-sand-50' }}">
                            <input type="radio" wire:model.live="payment" value="{{ $key }}" class="mt-1 accent-nile-800">
                            <span class="min-w-0">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold">{{ $method['label'] }}</span>
                                    <span class="rounded-full bg-sand-100 px-2 py-0.5 text-[11px] font-semibold text-nile-800">{{ $method['tag'] }}</span>
                                </span>
                                <span class="mt-1 block text-sm text-ink/60">{{ $method['hint'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('payment') <span class="mt-2 block text-xs text-deal">{{ $message }}</span> @enderror
            </section>
        </div>

        <aside class="rounded-[2rem] bg-white p-6 ring-1 ring-black/5 lg:sticky lg:top-28">
            <h2 class="font-semibold">{{ __('store.checkout.your_order') }}</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($lines as $line)
                    <li class="flex items-center gap-3 text-sm">
                        <img src="{{ $line['product']->imageUrl() }}" alt="" class="size-12 rounded-xl object-cover">
                        <span class="min-w-0 flex-1">
                            <span class="line-clamp-1 font-medium">{{ $line['product']->displayName() }}</span>
                            <span class="text-ink/45">{{ __('store.checkout.qty', ['count' => $line['qty']]) }}</span>
                        </span>
                        <span class="shrink-0">{{ \App\Support\Money::format($line['line']) }}</span>
                    </li>
                @endforeach
            </ul>

            <label class="mt-5 block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.checkout.promo', ['code' => $promoCode]) }}</span>
                <input wire:model.blur="promo" type="text" dir="ltr" class="mt-1 h-11 w-full rounded-2xl bg-sand-50 px-4 text-sm uppercase ring-1 ring-black/10 outline-none">
            </label>

            <dl class="mt-4 space-y-2 border-t border-sand-200 pt-4 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-ink/60">{{ __('store.checkout.subtotal') }}</dt><dd>{{ \App\Support\Money::format($subtotal) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-ink/60">{{ __('store.checkout.discount') }}</dt><dd>{{ $discount > 0 ? '−'.\App\Support\Money::format($discount) : '—' }}</dd></div>
                @if ($taxPercent > 0)
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink/60">
                            {{ $pricesIncludeTax
                                ? __('store.checkout.tax_included', ['percent' => $taxPercent])
                                : __('store.checkout.tax', ['percent' => $taxPercent]) }}
                        </dt>
                        <dd>{{ \App\Support\Money::format($tax) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3"><dt class="text-ink/60">{{ __('store.checkout.delivery') }}</dt><dd>{{ $quote['fee'] == 0 ? __('store.checkout.free') : \App\Support\Money::format($quote['fee']) }}</dd></div>
                <div class="flex items-end justify-between gap-3 pt-2 text-base">
                    <dt class="font-semibold">{{ __('store.checkout.total') }}</dt>
                    <dd class="text-2xl font-semibold">{{ \App\Support\Money::format($total) }}</dd>
                </div>
            </dl>
            <p class="mt-2 text-xs leading-5 text-ink/50">{{ __('store.checkout.free_note', ['eta' => $quote['eta'], 'amount' => $freeOver]) }}</p>
            <button type="submit" class="mt-5 w-full rounded-full bg-gold-500 py-3 text-sm font-semibold text-white hover:bg-gold-600" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="placeOrder">{{ __('store.checkout.place') }}</span>
                <span wire:loading wire:target="placeOrder">{{ __('store.checkout.placing') }}</span>
            </button>
        </aside>
    </form>
</div>
