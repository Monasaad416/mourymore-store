<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Js;

trait NotifiesCustomer
{
    protected function notifyCustomer(string $message): void
    {
        $this->js('window.storeToast('.Js::from($message).')');
        $this->dispatch('toast', message: $message);
    }
}
