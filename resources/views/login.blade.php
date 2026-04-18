@extends('layouts.auth')

@section('title', 'Login')

@push('styles')
    <style>
        body.auth-page-body {
            background: linear-gradient(178.48deg, #4397BB 1.29%, #FAFAFA 116%);
            font-family: 'Inter', sans-serif;
        }

        /*
         * Kartu sedikit lebih “gelap” dari field: opacity putih lebih rendah + blur
         * supaya field putih tidak menyatu (tidak samar). Bukan border tebal.
         */
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

        .logo {
            width: 60%;
            max-width: 200px;
            height: 60px;
            margin-bottom: 20px;
        }

        /* Field putih solid + bayangan halus agar terbaca jelas di atas kartu */
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

        /* Timpa gaya dark global (app.css) — tetap putih di halaman login */
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

        .input-icon,
        .password-toggle {
            position: absolute;
            top: 12px;
            color: #7D7D7D;
            font-size: 1.2rem;
        }

        .input-icon {
            left: 16px;
        }

        .password-toggle {
            right: 16px;
            cursor: pointer;
        }

        .btn-login {
            background: #000000;
            color: white;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
        }

        .btn.btn-login:hover {
            background: #000000 !important;
            color: white !important;
        }

        .forgot-password {
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            color: #111111;
            text-decoration: none;
            font-size: 13px;
        }

        .form-label {
            color: #7D7D7D;
            font-weight: 500;
        }

        .text-red {
            color: #EA4949;
        }

        @media (max-width: 567px) {
            .login-card {
                padding: 24px 18px;
            }

            .login-card .form-control {
                font-size: 13px;
                height: 44px;
            }

            .btn-login {
                height: 44px;
                font-size: 13px;
            }

            .input-icon,
            .password-toggle {
                font-size: 1rem;
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
        <div class="logo mx-auto">
            <img src="{{ asset('storage/images/logo.png') }}" alt="Crocodic" width="200px">
        </div>
        @if (session('error'))
            <div class="alert text-red align-items-center"><i class="bi bi-exclamation-circle-fill"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('loginsystem') }}">
            @csrf
            <div class="container px-5">
                <div class="mb-3 position-relative">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Email">
                </div>

                <div class="mb-2 position-relative">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                    <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                </div>

                <div class="d-flex justify-content-end mb-1">
                    <a href="{{ route('password.request') }}" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-login w-100 mt-4 mb-4">SIGN IN</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    </script>
@endpush
