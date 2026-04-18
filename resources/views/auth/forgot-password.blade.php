@extends('layouts.auth')

@section('title', 'Forgot Password')

@push('styles')
    <style>
        body.auth-page-body {
            background: linear-gradient(178.48deg, #4397BB 1.29%, #FAFAFA 116%);
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        /* Sama seperti halaman login */
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

        .login-card h2.forgot-title,
        .login-card p.forgot-desc {
            color: #000 !important;
        }

        .top-image {
            width: 120px;
            display: block;
            margin: 0 auto 20px auto;
        }

        .login-card .form-control {
            height: 48px;
            border-radius: 10px;
            padding-left: 42px;
            font-size: 1rem;
            border: none !important;
            background-color: #fff !important;
            color: #111 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
        }

        .login-card .form-control::placeholder {
            color: #5c5c5c;
        }

        .login-card .form-control:focus {
            border: none !important;
            outline: none;
            background-color: #fff !important;
            color: #111 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14), 0 0 0 1px rgba(67, 151, 187, 0.35) !important;
        }

        html[data-theme="dark"] body.auth-page-body .login-card {
            background: rgba(255, 255, 255, 0.52) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        html[data-theme="dark"] body.auth-page-body .login-card .form-control,
        html[data-theme="dark"] body.auth-page-body .login-card .form-control:focus {
            border: none !important;
            border-color: transparent !important;
            background-color: #fff !important;
            color: #111 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
        }

        html[data-theme="dark"] body.auth-page-body .login-card .form-control:focus {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14), 0 0 0 1px rgba(67, 151, 187, 0.35) !important;
        }

        html[data-theme="dark"] body.auth-page-body .login-card h2.forgot-title,
        html[data-theme="dark"] body.auth-page-body .login-card p.forgot-desc {
            color: #000 !important;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 12px;
            color: #7d7d7d;
            font-size: 1.2rem;
        }

        .btn-login {
            background: #000000;
            color: white;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
        }

        .btn-login:hover {
            background: #000000;
            color: white;
        }

        .back-login {
            font-weight: 600;
            color: #000 !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
        }

        .back-login:hover {
            color: #000 !important;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 24px 18px;
            }

            .login-card .form-control {
                font-size: 0.9rem;
                height: 44px;
            }

            .btn-login {
                height: 44px;
                font-size: 0.9rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-card text-center">

        <img src="{{ asset('storage/images/P1.png') }}" class="top-image" alt="Forgot Image" width="500px">

        <h2 class="forgot-title mb-1" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
            Forgot Your Password
        </h2>

        <p class="forgot-desc px-4 mb-3" style="font-size: 14px;">
            Enter your email to get new password
        </p>

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="container px-5">
                <div class="mb-3 position-relative">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>

                <button type="submit" class="btn btn-login w-100 mt-3 mb-3">
                    Send Email
                </button>

                <a href="{{ route('loginsystem') }}" class="back-login">
                    <i class="bi bi-chevron-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
@endsection
