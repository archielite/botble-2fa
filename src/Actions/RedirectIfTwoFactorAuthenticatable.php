<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Botble\ACL\Traits\AuthenticatesUsers;
use Closure;
use Illuminate\Http\Request;

class RedirectIfTwoFactorAuthenticatable
{
    use AuthenticatesUsers;

    public function handle(Request $request, Closure $next)
    {
        if (! $this->guard()->once($this->credentials($request))) {
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse();
        }

        $user = $this->guard()->user();

        if (TwoFactor::userHasEnabled($user)) {
            session()->put('login.id', $user->getKey());
            session()->put('login.remember', $request->has('remember'));

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }

    public function username()
    {
        return filter_var(request()->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }
}
