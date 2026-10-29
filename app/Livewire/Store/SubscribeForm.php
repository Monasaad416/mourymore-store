<?php

namespace App\Livewire\Store;

use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class SubscribeForm extends Component
{
    public string $email = '';

    public bool $done = false;

    public function subscribe(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        $data = $this->validate([
            'email' => ['required', 'email', 'max:255', 'unique:subscriptions,email'],
        ], [
            'email.unique' => __('store.content.already_subscribed'),
        ]);

        Subscription::query()->create([
            'email' => $data['email'],
            'locale' => app()->getLocale(),
        ]);

        $this->reset('email');
        $this->done = true;
    }

    public function render(): View
    {
        return view('livewire.store.subscribe-form', [
            'available' => Schema::hasTable('subscriptions'),
        ]);
    }
}
