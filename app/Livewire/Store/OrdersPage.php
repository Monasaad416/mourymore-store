<?php

namespace App\Livewire\Store;

use App\Models\Order;
use App\Support\Payments;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class OrdersPage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function render(): View
    {
        /** @var Collection<int, Order> $orders */
        $orders = auth()->user()
            ->orders()
            ->with('items')
            ->latest('id')
            ->get();

        return view('livewire.store.orders-page', [
            'orders' => $orders,
            'paymentLabels' => $orders
                ->mapWithKeys(fn (Order $order): array => [
                    $order->id => Payments::label($order->payment_method),
                ])
                ->all(),
        ])->title(__('store.orders.meta_title'));
    }
}
