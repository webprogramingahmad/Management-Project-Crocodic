@extends('layouts.auth')

@section('title', 'Reset Password')

@push('styles')
    <style>
        body.auth-page-body {
            background: linear-gradient(178.48deg, #4397BB 1.29%, #FAFAFA 116%);
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        /* Sama seperti login & forgot password */
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

        .login-card h2.reset-title,
        .login-card p.reset-desc {
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

        html[data-theme="dark"] body.auth-page-body .login-card h2.reset-title,
        html[data-theme="dark"] body.auth-page-body .login-card p.reset-desc {
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

        @media (min-width: 1200px) {
            .login-card {
                max-width: 540px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-card text-center">

        <img src="{{ asset('storage/images/P3.png') }}" class="top-image" alt="Reset Password">

        <h2 class="reset-title mb-1" style="font-family: 'Montserrat', sans-serif; font-weight: 600;">
            Reset password
        </h2>

        <p class="reset-desc px-4 mb-3" style="font-size: 14px;">
            Enter your new password
        </p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="container px-5">
                <div class="mb-3 position-relative text-start">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        placeholder="Password"
                        required
                        autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 position-relative text-start">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                        class="form-control"
                        name="password_confirmation"
                        placeholder="Confirm Password"
                        required
                        autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-login w-100 mt-3 mb-3">
                    Reset Password
                </button>
            </div>
        </form>

    </div>
@endsection
