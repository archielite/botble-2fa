<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RedirectIfCustomerTwoFactorAuthenticatable
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('customer');

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (! $guard->once($credentials)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $customer = $guard->user();

        if (TwoFactor::userHasEnabled($customer)) {
            session()->put('customer_login.id', $customer->getKey());
            session()->put('customer_login.remember', $request->filled('remember'));

            return redirect()->route('customer.two-factor.challenge');
        }

        return $next($request);
    }
}
