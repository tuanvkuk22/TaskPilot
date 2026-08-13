<?php

namespace ProjectsManager\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\User;

class SocialAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (Exception $exception) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Unable to authenticate with Google. Please try again.']);
        }

        if (empty($socialUser->getEmail()) || empty($socialUser->getId())) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Google authentication did not return a valid email address.']);
        }

        $user = User::where('provider', 'google')
            ->where('provider_id', $socialUser->getId())
            ->first();

        $avatar = $socialUser->getAvatar();

        if ($user === null) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user !== null) {
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $avatar ?: $user->avatar,
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $avatar,
                    'password' => Hash::make(uniqid('google-', true)),
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'status' => 'active',
                ]);
            }
        }

        Auth::login($user, true);

        $user->last_login_at = now();
        $user->save();

        return redirect()->intended('/home');
    }
}
