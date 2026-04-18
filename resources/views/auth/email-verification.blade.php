@extends('layouts.auth')

@section('title', 'Email Verification')

@push('styles')
    <style>
        body.auth-page-body {
            background: linear-gradient(178.48deg, #4397BB 1.29%, #FAFAFA 116%);
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        .verification-card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.75);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.25);
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
        }

        .top-image {
            width: 120px;
            display: block;
            margin: 0 auto 20px auto;
        }

        .btn-back {
            background: #000000;
            color: white;
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
            line-height: 48px;
        }

        .btn-back:hover {
            background: #000000;
            color: white;
            text-decoration: none;
        }

        @media (max-width: 576px) {
            .verification-card {
                padding: 24px 18px;
            }

            .btn-back {
                height: 44px;
                font-size: 0.9rem;
                line-height: 44px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="verification-card">

        <img src="{{ asset('storage/images/P2.png') }}" class="top-image" alt="Verification Icon">

        <h2 class="mb-3" style="font-family: 'Montserrat'; font-weight: 600;">
            Check Your Email
        </h2>

        <p class="px-4" style="font-size: 14px;">
            We have sent a verification link to your email address. Please check your inbox and click the link to verify your email.
        </p>

        <a href="https://mail.google.com" target="_blank" rel="noopener noreferrer" class="btn-back">
            <i class="bi bi-envelope-open"></i> Open Gmail
        </a>

    </div>
@endsection
