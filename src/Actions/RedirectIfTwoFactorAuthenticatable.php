<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use Botble\ACL\Traits\AuthenticatesUsers;
use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfTwoFactorAuthenticatable
{
    use AuthenticatesUsers;

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::once($request->only(['username', 'password']))) {
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse();
        }

        $user = Auth::user();

        if (TwoFactor::userHasEnabled($user)) {
            session()->put('login.id', $user->getKey());
            session()->put('login.remember', $request->has('remember'));

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
