<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Only allow the providers we support.
     */
    private array $providers = ['google', 'github'];

    /**
     * Map provider => scopes.
     */
    private function scopesFor(string $provider): array
    {
        return match ($provider) {
            'google' => ['openid', 'profile', 'email'],
            'github' => ['read:user', 'user:email'],
            default  => [],
        };
    }

    /**
     * /auth/{provider}/redirect
     */
    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->providers, true), 404);

        $callback = route('oauth.callback', ['provider' => $provider]);

        $driver = Socialite::driver($provider)
            ->redirectUrl($callback)
            ->scopes($this->scopesFor($provider));

        // Nice UX for Google (optional)
        if ($provider === 'google') {
            $driver = $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    /**
     * /auth/{provider}/callback
     */
    public function callback(string $provider)
    {
        abort_unless(in_array($provider, $this->providers, true), 404);

        $callback = route('oauth.callback', ['provider' => $provider]);

        try {
            // Use stateless to avoid invalid_state issues behind proxies/CDNs.
            $socialUser = Socialite::driver($provider)
                ->redirectUrl($callback)
                ->stateless()
                ->user();
        } catch (\Throwable $e) {
            Log::error("{$provider} OAuth callback error", ['exception' => $e]);
            return redirect()
                ->route('login')
                ->with('error', 'Could not sign you in with '.$provider.'. Please try again.');
        }

        // 1) Already linked?
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

        // 2) Not linked yet — find or create user by email
        $email = $socialUser->getEmail(); // may be null for some GitHub accounts
        $user  = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            $fallbackEmail = $email ?: (Str::uuid().'@no-email.local');
            $name = $socialUser->getName()
                ?: ($email ? Str::before($email, '@') : 'User');

            $user = User::create([
                'name'              => $name,
                'email'             => $fallbackEmail,
                'email_verified_at' => $email ? now() : null, // only verify if we actually have their email
                'password'          => bcrypt(Str::random(40)),
            ]);
        }

        // 3) Link the social account (idempotent)
        SocialAccount::updateOrCreate(
            [
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'user_id'       => $user->id,
                'avatar'        => $socialUser->getAvatar(),
                'token'         => $socialUser->token ?? null,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'expires_in'    => $socialUser->expiresIn ?? null,
            ]
        );

        Auth::login($user, remember: true);
        return redirect()->intended(route('homepage'));
    }
}
