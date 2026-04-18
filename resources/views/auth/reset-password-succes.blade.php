@extends('layouts.auth')

@section('title', 'Reset Password Success')

@push('styles')
    <style>
        body.auth-page-body {
            background: linear-gradient(178.48deg, #4397BB 1.29%, #FAFAFA 116%);
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        /* Selaras dengan login, forgot password, reset password */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.52);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.18);
            border-radius: 20px;
            padding: 32px 24px;
            margin: auto;
        }

        .login-card h2.success-title,
        .login-card p.success-desc {
            color: #000 !important;
        }

        .top-image {
            width: 120px;
            display: block;
            margin: 0 auto 20px auto;
        }

        html[data-theme="dark"] body.auth-page-body .login-card {
            background: rgba(255, 255, 255, 0.52) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        html[data-theme="dark"] body.auth-page-body .login-card h2.success-title,
        html[data-theme="dark"] body.auth-page-body .login-card p.success-desc {
            color: #000 !important;
        }

        .btn-login {
            background: #000000;
            color: white !important;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
        }

        .btn-login:hover {
            background: #000000;
            color: white !important;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 24px 18px;
            }

            .btn-login {
                height: 44px;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 1200px) {
            .login-card {
                max-width: 540px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-card text-center">

        <img src="{{ asset('storage/images/P4.png') }}"
            alt="Reset Success"
            class="top-image">

        <h2 class="success-title mb-1" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
            Reset password
        </h2>

        <p class="success-desc px-4 mb-4" style="font-size: 14px;">
            Your password has been successfully changed
        </p>

        <div class="container px-5">
            <a href="{{ route('loginsystem') }}" class="btn btn-login w-100 mt-1 text-decoration-none d-inline-flex align-items-center justify-content-center">
                Login now
            </a>
        </div>

    </div>
@endsection
