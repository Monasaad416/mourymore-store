<div class="mx-auto max-w-3xl px-4 py-10">
    @if (session('store.payment_success'))
        <p class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('store.payment_success') }}</p>
    @endif
    @if (session('store.payment_error'))
        <p class="mb-4 rounded-2xl bg-deal/10 px-4 py-3 text-sm text-deal">{{ session('store.payment_error') }}</p>
    @endif

    <div class="rounded-[2rem] bg-nile-900 px-6 py-8 text-white sm:px-10">
        <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.order.placed') }}</p>
        <h1 class="font-display mt-3 text-4xl sm:text-5xl">{{ __('store.order.thanks', ['name' => $order->customer_name]) }}</h1>
        <p class="mt-6 font-display text-2xl text-gold-300">{{ $order->number }}</p>
        @if ($canPay)
            <a href="{{ route('paymob.go', $order) }}" class="mt-6 inline-flex rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-nile-950 hover:bg-gold-400">
                {{ __('store.order.pay_now') }}
            </a>
        @endif
    </div>

    @if ($order->user_id === null)
        <div class="mt-4 rounded-2xl bg-deal/10 px-4 py-3 text-sm font-semibold leading-6 text-deal ring-1 ring-deal/20 sm:px-5">
            @if (filled($order->email))
                {{ __('store.order.keep_number_with_email') }}
            @else
                {{ __('store.order.keep_number') }}
            @endif
        </div>
    @endif

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
                            <a href="{{ $item->entitlement->downloadUrl() }}" class="mt-2 inline-flex items-center gap-2 rounded-full bg-gold-500 px-4 py-2 text-base font-bold text-nile-950 hover:bg-gold-400">
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v10m0 0 4-4m-4 4-4-4" />
                                    <path stroke-linecap="round" d="M5 18h14" />
                                </svg>
                                {{ __('store.order.download') }}
                            </a>
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
