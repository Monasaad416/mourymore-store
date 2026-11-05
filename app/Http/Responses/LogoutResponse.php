<?php

namespace App\Http\Responses;

use App\Services\Cart;
use App\Support\CustomerFlash;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        app(Cart::class)->releaseCookie();

        [$message, $cookie] = CustomerFlash::redirectWith('logout', __('store.auth.flash.logout'));

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect(Fortify::redirects('logout', route('home')))
                ->with('flash_success', $message)
                ->withCookie($cookie);
    }
}
