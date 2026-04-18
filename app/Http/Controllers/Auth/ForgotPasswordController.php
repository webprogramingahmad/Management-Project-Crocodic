<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('email.verification')
                ->with(['status' => 'Link reset password sudah dikirim ke email kamu. Silakan cek inbox/spam.']);
        }

        return redirect()
            ->route('email.verification')
            ->with(['status' => 'Email tidak ditemukan atau pengiriman gagal. Silakan cek kembali email kamu.']);
    }
}
