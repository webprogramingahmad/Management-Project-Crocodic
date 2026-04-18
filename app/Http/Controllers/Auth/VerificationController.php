<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // Tampilkan form kirim email
    public function showEmailForm()
    {
        return view('auth.send-email'); // pastikan blade ini ada: auth/send-email.blade.php
    }

    // Proses submit email, lalu redirect ke halaman verifikasi email
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Logika pengiriman email disini (contoh sederhana, atau panggil service email)
        // Mail::to($request->email)->send(new YourVerificationMail());

        // Simpan session untuk pesan sukses
        $request->session()->flash('status', 'Verification email has been sent! Please check your inbox.');

        // Redirect ke halaman email verification
        return redirect()->route('email.verification');
    }

    // Tampilkan halaman verifikasi email
    public function showVerificationPage()
    {
        return view('auth.email-verification');
    }
}
