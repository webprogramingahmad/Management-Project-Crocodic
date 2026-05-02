@extends('layouts.app')

@section('title')
    Account {{ $user->name }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/profile.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .btn-hitam {
            background-color: #000 !important;
            color: #fff !important;
            width: 120px;
            height: 35px;
            border-radius: 20%;
            font-weight: 400;
            font-size: 0.875rem;
            font-family: 'Montserrat', sans-serif;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
        }

        .btn-hitam:hover {
            background-color: #222 !important;
        }

        .btn-admin-show-edit {
            border: 1px solid rgba(224, 224, 224, 0.7) !important;
            border-radius: 0.5rem !important;
            box-sizing: border-box;
        }

        .btn-admin-show-edit:hover {
            border-color: rgba(208, 208, 208, 0.8) !important;
        }

        html[data-theme="dark"] .btn-admin-show-edit {
            border-color: rgba(161, 161, 170, 0.35) !important;
        }

        html[data-theme="dark"] .btn-admin-show-edit:hover {
            border-color: rgba(161, 161, 170, 0.55) !important;
        }

        .admin-show-user-name {
            color: #212529;
        }

        html[data-theme="dark"] .admin-show-user-name {
            color: #ffffff !important;
        }

        .profile-left-panel {
            padding-top: 0.25rem;
        }

        .profile-avatar-wrap {
            margin-right: 3.25rem !important;
        }

        .profile-metrics-wrap {
            margin-top: 1.25rem;
        }

        .profile-data-actions {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .profile-data-grid {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .profile-shell-card {
            border-color: rgba(224, 224, 224, 0.7) !important;
        }

        .profile-shell-card .card-body {
            padding: 1.5rem !important;
        }

        .profile-main-wrap {
            margin: 0 !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }

        .profile-data-card {
            border-radius: 15px;
            max-width: 100%;
            border-color: rgba(224, 224, 224, 0.7) !important;
        }

        .profile-data-card .card-body {
            padding-bottom: 2.25rem !important;
        }

        html[data-theme="dark"] .profile-shell-card,
        html[data-theme="dark"] .profile-data-card {
            border-color: rgba(161, 161, 170, 0.35) !important;
        }
    </style>
@endsection

@php
    $initial = strtoupper(substr($user->name, 0, 1));
    $viewedRole = $user->role?->role ?? '';
@endphp

@section('content')
    <div class="container-fluid px-1 py-1">
        <div class="card profile-shell-card" style="border-radius: 15px;">
            <div class="card-body">
                <div class="container-fluid profile-main-wrap">
                    <form id="profileForm" action="{{ route('executive.accounts.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row font-montserrat align-items-start">
                            <div class="col-12 col-lg-4 mb-4 mb-lg-0 profile-left-panel">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 profile-avatar-wrap">
                                        @if ($user->avatar)
                                            <img id="avatarPreview" alt="Foto Profil" class="profile-pic rounded-circle"
                                                src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                                style="width: 150px; height: 150px; object-fit: cover;" />
                                        @else
                                            <div id="avatarPreview"
                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                style="width: 150px; height: 150px; font-size: 82px; background-color: #D9D9D9; color: white;">
                                                {{ $initial }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="fw-semibold fs-4 mb-0 admin-show-user-name">{{ Str::ucfirst($user->name) }}</h5>
                                        <p class="fw-normal text-secondary mb-1 fs-6">
                                            @if ($viewedRole === 'staff')
                                                {{ Str::ucfirst($user->division?->divisi ?? '-') }}
                                            @else
                                                {{ \App\Support\RoleDisplay::label($viewedRole ?: null) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="font-montserrat profile-metrics-wrap">
                                    <div class="d-flex align-items-center justify-content-start mb-3">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Project Total</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5"
                                            style="min-width: 110px">
                                            <span class="fw-semibold fs-5">{{ $projects_joined_count }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-start mb-3">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Tasks Done</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5"
                                            style="min-width: 110px">
                                            <span class="fw-semibold fs-5">{{ $completed_task_count }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-start mb-5">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Total Leave</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5"
                                            style="min-width: 110px">
                                            <span class="fw-semibold fs-5">{{ $accepted_absent_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-8">
                                <div class="card profile-data-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-end mb-3 profile-data-actions">
                                            <a href="{{ route('executive.accounts.edit', $user->id) }}" id="editBtn"
                                                class="btn btn-hitam btn-admin-show-edit" type="button">Edit</a>
                                        </div>
                                        <div class="row gx-3 profile-data-grid">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="email" class="form-label" style="color: #7D7D7D">Email</label>
                                                <input type="email" class="form-control" name="email" id="email"
                                                    value="{{ $user->email }}" readonly />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="telegram" class="form-label" style="color: #7D7D7D">Link Telegram</label>
                                                <input type="text" class="form-control" name="link_tele" id="link_tele"
                                                    value="{{ $user->link_tele }}" readonly />
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="phone" class="form-label" style="color: #7D7D7D">Phone Number</label>
                                                <input type="text" class="form-control" name="no_telp" id="no_telp"
                                                    value="{{ $user->no_telp }}" readonly />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="birth" class="form-label" style="color: #7D7D7D">Birth</label>
                                                <input type="text" class="form-control" name="tgl_lahir" id="tgl_lahir"
                                                    value="{{ $user->tgl_lahir?->format('d M Y') ?? '' }}" readonly />
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="address" class="form-label" style="color: #7D7D7D">Address</label>
                                                <input type="text" class="form-control" name="alamat" id="alamat"
                                                    value="{{ $user->alamat }}" readonly />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="join-date" class="form-label" style="color: #7D7D7D">Join Date</label>
                                                <input type="text" class="form-control" name="tgl_masuk" id="join-date"
                                                    value="{{ $user->tgl_masuk?->format('d M Y') ?? '' }}" readonly />
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="nik" class="form-label" style="color: #7D7D7D">NIK</label>
                                                <input type="text" class="form-control" name="nik" id="nik"
                                                    value="{{ $user->nik }}" readonly />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="employment-status" class="form-label" style="color: #7D7D7D">Employment Status</label>
                                                <input type="text" class="form-control" name="employment_status" id="employment-status"
                                                    value="{{ $user->statussdm?->status_sdm ?? '-' }}" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
