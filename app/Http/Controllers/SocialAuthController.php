<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    // /auth/{provider}/redirect
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // /auth/{provider}/callback
    public function callback(string $provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        // Try to find an existing link first
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($account) {
            $account->update([
                'avatar'        => $socialUser->getAvatar(),
                'token'         => $socialUser->token ?? null,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'expires_in'    => $socialUser->expiresIn ?? null,
            ]);
            Auth::login($account->user, remember: true);
            return redirect()->intended(route('homepage'));
        }

        // No link yet — match by email if present
        $email = $socialUser->getEmail();
        $user = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            // Create a new user (minimal fields)
            $user = User::create([
                'name'  => $socialUser->getName() ?: Str::before($email, '@') ?: 'User',
                'email' => $email ?? Str::uuid().'@no-email.local',
                'email_verified_at' => $email ? now() : null, // Apple/Facebook may hide email
                'password' => bcrypt(Str::random(32)),        // random, not used for social sign-in
            ]);
        }

        // Link the social account
        $user->socialAccounts()->create([
            'provider'      => $provider,
            'provider_id'   => $socialUser->getId(),
            'avatar'        => $socialUser->getAvatar(),
            'token'         => $socialUser->token ?? null,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'expires_in'    => $socialUser->expiresIn ?? null,
        ]);

        Auth::login($user, remember: true);
        return redirect()->intended(route('homepage'));
    }
}
