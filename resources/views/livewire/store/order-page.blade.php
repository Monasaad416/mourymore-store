<div class="mx-auto max-w-3xl px-4 py-10">
    <div class="rounded-[2rem] bg-nile-900 px-6 py-8 text-white sm:px-10">
        <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.order.placed') }}</p>
        <h1 class="font-display mt-3 text-4xl sm:text-5xl">{{ __('store.order.thanks', ['name' => $order->customer_name]) }}</h1>
        <p class="mt-3 text-white/75">{{ __('store.order.sms', ['phone' => $order->phone]) }}</p>
        <p class="mt-6 font-display text-2xl text-gold-300">{{ $order->number }}</p>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        @foreach (__('store.order.steps') as $index => $step)
            <div class="rounded-3xl bg-white p-4 ring-1 ring-black/5 {{ $index === 0 ? 'ring-nile-700' : '' }}">
                <p class="text-xs text-ink/40">0{{ $index + 1 }}</p>
                <p class="mt-1 font-semibold">{{ $step }}</p>
                <p class="mt-1 text-xs text-ink/50">{{ $index === 0 ? __('store.order.just_now') : $quote['eta'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-4 rounded-[2rem] bg-white p-6 ring-1 ring-black/5">
        <h2 class="font-semibold">{{ __('store.order.delivery') }}</h2>
        <p class="mt-2 text-sm leading-6">
            {{ $order->address }}<br>
            {{ __('store.order.city_line', ['city' => $order->city, 'governorate' => \App\Support\Shipping::label($order->governorate)]) }}
        </p>
        <p class="mt-3 text-sm text-ink/60">{{ __('store.order.payment', ['method' => $paymentLabel]) }}</p>
        @if ($order->notes)
            <p class="mt-2 text-sm text-ink/60">{{ __('store.order.note', ['note' => $order->notes]) }}</p>
        @endif

        <ul class="mt-5 space-y-3 border-t border-sand-200 pt-5">
            @foreach ($order->items as $item)
                <li class="flex items-center gap-3 text-sm">
                    <img src="{{ $item->imageUrl() }}" alt="" class="size-14 rounded-xl object-cover">
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium">{{ $item->displayName() }}</span>
                        <span class="text-ink/45">{{ __('store.order.qty', ['count' => $item->qty]) }}</span>
                        @if ($item->product_type === 'digital' && $item->entitlement)
                            <a href="{{ $item->entitlement->downloadUrl() }}" class="mt-1 block text-sm font-semibold text-nile-800">{{ __('store.order.download') }}</a>
                        @elseif ($item->product_type === 'digital')
                            <span class="mt-1 block text-ink/45">{{ __('store.order.access_pending') }}</span>
                        @endif
                    </span>
                    <span>{{ \App\Support\Money::format($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>

        <dl class="mt-5 space-y-1 border-t border-sand-200 pt-4 text-sm">
            <div class="flex justify-between gap-3"><dt class="text-ink/55">{{ __('store.order.subtotal') }}</dt><dd>{{ \App\Support\Money::format($order->subtotal) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-ink/55">{{ __('store.order.discount') }}</dt><dd>{{ (float) $order->discount > 0 ? '−'.\App\Support\Money::format($order->discount) : '—' }}</dd></div>
            @if ((float) $order->tax > 0)
                <div class="flex justify-between gap-3"><dt class="text-ink/55">{{ __('store.order.tax') }}</dt><dd>{{ \App\Support\Money::format($order->tax) }}</dd></div>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-ink/55">{{ __('store.order.delivery_fee') }}</dt><dd>{{ (float) $order->shipping == 0 ? __('store.order.free') : \App\Support\Money::format($order->shipping) }}</dd></div>
            <div class="flex justify-between gap-3 pt-2 text-base font-semibold"><dt>{{ __('store.order.total') }}</dt><dd>{{ \App\Support\Money::format($order->total) }}</dd></div>
        </dl>
    </section>

    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-white" wire:navigate>{{ __('store.order.keep') }}</a>
</div>
