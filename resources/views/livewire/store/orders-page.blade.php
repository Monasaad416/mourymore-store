<div class="mx-auto max-w-3xl px-4 py-10">
    <p class="text-xs font-semibold text-nile-700 uppercase">{{ __('store.account.kicker') }}</p>
    <h1 class="font-display mt-1 text-4xl">{{ __('store.orders.title') }}</h1>
    <p class="mt-2 text-sm text-ink/55">{{ __('store.orders.lead') }}</p>

    <nav class="mt-6 flex flex-wrap gap-2" aria-label="{{ __('store.account.kicker') }}">
        <a href="{{ route('profile.edit') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-black/10 hover:bg-sand-50" wire:navigate>
            {{ __('store.account.profile') }}
        </a>
        <a href="{{ route('orders.index') }}" class="rounded-full bg-nile-900 px-4 py-2 text-sm font-semibold text-white" wire:navigate>
            {{ __('store.nav.orders') }}
        </a>
    </nav>

    @if ($orders->isEmpty())
        <div class="mt-8 rounded-[2rem] bg-white p-8 text-center ring-1 ring-black/5">
            <p class="font-semibold">{{ __('store.orders.empty') }}</p>
            <p class="mt-2 text-sm text-ink/55">{{ __('store.orders.empty_lead') }}</p>
            <a href="{{ route('shop') }}" class="mt-6 inline-flex rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-white hover:bg-gold-600" wire:navigate>
                {{ __('store.orders.shop') }}
            </a>
        </div>
    @else
        <ul class="mt-8 space-y-3">
            @foreach ($orders as $order)
                <li>
                    <a href="{{ route('orders.show', $order) }}" class="block rounded-[1.75rem] bg-white p-5 ring-1 ring-black/5 transition hover:ring-nile-700" wire:navigate>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold">{{ $order->number }}</p>
                                <p class="mt-1 text-sm text-ink/55">
                                    {{ $order->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                                    · {{ $paymentLabels[$order->id] ?? $order->payment_method }}
                                </p>
                            </div>
                            <div class="text-end">
                                <p class="text-lg font-semibold">{{ \App\Support\Money::format((float) $order->total) }}</p>
                                <p class="mt-1 text-xs font-medium text-ink/50">{{ __('store.orders.status.'.$order->payment_status) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 line-clamp-1 text-sm text-ink/60">
                            {{ $order->items->map->displayName()->join(' · ') }}
                        </p>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
