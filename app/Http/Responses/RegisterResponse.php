<?php

namespace App\Http\Responses;

use App\Support\CustomerFlash;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        [$message, $cookie] = CustomerFlash::redirectWith('register', __('store.auth.flash.register'));

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => Fortify::redirects('register', route('home'))], 201)
            : redirect()
                ->intended(Fortify::redirects('register', route('home')))
                ->with('flash_success', $message)
                ->withCookie($cookie);
    }
}
