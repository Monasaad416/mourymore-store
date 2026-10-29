<?php

namespace App\Livewire\Actions;

use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): Redirector|RedirectResponse
    {
        app(Cart::class)->releaseCookie();

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
