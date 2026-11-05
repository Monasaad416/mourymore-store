<?php

namespace App\Http\Responses;

use App\Support\CustomerFlash;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        [$message, $cookie] = CustomerFlash::redirectWith('login', __('store.auth.flash.login'));

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()
                ->intended(Fortify::redirects('login', route('home')))
                ->with('flash_success', $message)
                ->withCookie($cookie);
    }
}
