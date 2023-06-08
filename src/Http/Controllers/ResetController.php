<?php

namespace Whitecube\LaravelCookieConsent\Http\Controllers;

use Whitecube\LaravelCookieConsent\CookiesManager;
use Illuminate\Http\Request;

class ResetController
{
    public function __invoke(Request $request, CookiesManager $cookies)
    {
        // TODO.

        return redirect()->back()->withoutCookie(
            cookie: config('cookieconsent.cookie.name'),
            domain: config('cookieconsent.cookie.domain'),
        );
    }
}
