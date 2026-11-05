<?php

namespace App\Livewire\Store;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Payments;
use App\Support\Shipping;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.store')]
class OrderPage extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items.entitlement', 'payments']);
    }

    public function render(): View
    {
        $payment = $this->order->payments->sortByDesc('id')->first();
        $canPay = $payment instanceof Payment
            && $payment->gateway === 'paymob'
            && in_array($payment->status, ['pending', 'failed'], true)
            && $this->order->payment_status !== 'paid';

        return view('livewire.store.order-page', [
            'paymentLabel' => Payments::label($this->order->payment_method),
            'quote' => Shipping::quote($this->order->governorate, (float) $this->order->subtotal),
            'canPay' => $canPay,
        ])->title(__('store.order.meta_title', ['number' => $this->order->number]));
    }
}
