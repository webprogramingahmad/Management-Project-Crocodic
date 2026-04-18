<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\PasswordBroker as LaravelPasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->email;

        if (!$token || !$email) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Link reset password tidak valid atau sudah kadaluarsa.']);
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->first();

        /** @var LaravelPasswordBroker $passwordBroker */
        $passwordBroker = Password::broker();

        if (!$user || !$passwordBroker->tokenExists($user, $token)) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Link reset password tidak valid atau sudah kadaluarsa.']);
        }

        return view('auth.reset-password')->with([
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/reset-password-success';
}
