@extends('layouts.app')

@section('title')
    Profile - {{ Auth::user()->name }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/profile.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
@endsection

@php
    $role = Auth::user()->role->role;
    $initial = strtoupper(substr($user->name, 0, 1));
@endphp
<head>
<style>
.btn-hitam {
    background-color: #000 !important; /* Warna latar belakang hitam */
    color: #fff !important;            /* Warna teks putih */
    width: 120px;                       /* Lebar tombol */
    height: 35px;                      /* Tinggi tombol */
    border-radius: 20%;                  /* perbaikan dari corner-radius */
    font-weight: 400;                   /* Membuat teks lebih tebal */
    font-size: 0,5rem;                 /* Ukuran font */
    font-family: 'montserrat', sans-serif;  /* Menggunakan font Montserrat */

    /* Agar teks tepat di tengah */
    justify-content: center !important;        
    align-items: center !important; 
    text-align: center !important;
}

.btn-hitam:hover {
    background-color: #222 !important;  /* sedikit lebih terang saat hover */
}

/* Tombol Edit profil (semua role): pinggiran jelas seperti tombol Cancel / chip border */
.btn-profile-edit {
    border: 1px solid #212529 !important;
    box-sizing: border-box;
}
.btn-profile-edit:hover {
    border-color: #000 !important;
}
html[data-theme="dark"] .btn-profile-edit {
    border-color: rgba(255, 255, 255, 0.45) !important;
}
html[data-theme="dark"] .btn-profile-edit:hover {
    border-color: rgba(255, 255, 255, 0.65) !important;
}

/* Shadow kustom untuk card */
.card-custom-shadow {
    box-shadow:
        8px 8px 14px rgba(0, 0, 0, 0.15),
        -8px -8px 14px rgba(255, 255, 255, 0.4);     
}

.profile-page-user-name {
    color: #212529;
}

html[data-theme="dark"] .profile-page-user-name {
    color: #f4f4f5 !important;
}

html[data-theme="dark"] .profile-page-user-role {
    color: #a1a1aa !important;
}

    </style>
</head>
@section('content')
    <div class="container-fluid px-1 py-1">
        <div class="card" style="border-radius: 15px; border-color: #E0E0E0CE;">
            <div class="card-body me-4">
                <div class="container-fluid mx-lg-3 mt-lg-4">
                    <div class="row">
                        <div class="col-6 col-md-3 col-xl-2">
                            @if ($user->avatar)
                                <img id="avatarPreview" alt="Foto Profil" class="profile-pic rounded-circle"
                                    src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                    style="width: 120px; height: 120px; object-fit: cover;" />
                            @else
                                <div id="avatarPreview"
                                    class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                    style="width: 120px; height: 120px; font-size: 70px; background-color: #D9D9D9; color: white;">
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>
                        <div class="font-montserrat col-6 col-md-9 col-xl-10" style="margin-left: -70px;">
                            <div class="mt-4 mb-2">
                                <div class="row">
                                    <div class="col-12 mb-0">
                                        <h5 class="fw-semibold fs-4 mb-0 profile-page-user-name">{{ Str::ucfirst(Auth::user()->name) }}</h5>
                                        <p class="fw-normal text-secondary mb-1 fs-6 profile-page-user-role">
                                            @if ($role === 'staff')
                                                {{ Str::ucfirst(Auth::user()->division->divisi) }}
                                            @else
                                                {{ \App\Support\RoleDisplay::label(Auth::user()->role->role ?? null) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="profileForm" method="POST"
                        action="{{ $role === 'staff' ? route('staff.profile.update', $user->id) : ($role === 'director' ? route('director.profile.update', $user->id) : route('executive.profile.update', $user->id)) }}">
                        @csrf
                        @method('PUT')
                        <div class="row font-montserrat">
                            <div class="col-12 col-lg-4 mb-4 mb-lg-0">
                                <div class="font-montserrat mb-2 mb-lg-0 mt-5">
                                    <div class="d-flex align-items-center justify-content-start mb-3">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Project Total</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5" style="min-width: 110px">
                                            <span class="fw-semibold fs-5">{{ $projects_joined_count }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-start mb-3">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Tasks Done</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5" style="min-width: 110px">
                                            <span class="fw-semibold fs-5">{{ $completed_task_count }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-start mb-5">
                                        <h5 class="text-secondary" style="width: 170px; font-weight: 350">Total Leave</h5>
                                        <div class="d-inline-block px-3 py-1 border rounded-2 text-center w-auto ms-5" style="min-width: 110px" >
                                            <span class="fw-semibold fs-5">{{ $accepted_absent_count }}</span>
                                        </div>
                                    </div>
                                   <!-- <div class="mt-3 mb-3 mb-md-5">
                                        <div class="text-secondary" style="width: 200px; font-weight: 400">Work hours</div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="progress position-relative w-progress"
                                                style="width: 330px; height: 25px; border-radius: 20px; overflow: hidden;" color="#7D7D7D">
                                                <div class="progress-bar d-flex align-items-end justify-content-center pe-2"
                                                    style="width: 30px; border-radius: 20px; padding-right: 8px;">
                                                    <small class="text-white">0%</small> 
                                                </div> 
                                            </div> -->
                                            {{-- <div class="ms-2 d-flex align-items-center">
                                                <i class="bi bi-exclamation-circle-fill text-black-50 me-1"
                                                    title="Over work"></i>
                                                <small class="text-muted">Over work</small> 
                                            </div> 
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                    <div class="col-12 col-lg-8">
                        <div class="card" style="border-radius: 15px; max-width: 100%; border-color: #E0E0E0CE;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-end mb-3">
                                            @if ($role === 'staff')
                                                <a href="{{ route('staff.profile.edit', $user->id) }}" id="editBtn"
                                                    class="btn btn-hitam btn-profile-edit" type="button">Edit</a>
                                            @elseif($role === 'director')
                                                <a href="{{ route('director.profile.edit', $user->id) }}" id="editBtn"
                                                    class="btn btn-hitam btn-profile-edit" type="button">Edit</a>
                                            @else
                                                <a href="{{ route('executive.profile.edit', $user->id) }}" id="editBtn"
                                                    class="btn btn-hitam btn-profile-edit" type="button">Edit</a>
                                            @endif
                                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2">
                                <div class="mb-3">
                                    <label for="email" class="form-label" style="color: 7D7D7D">Email</label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        value="{{ Auth::user()->email }}" readonly />
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label" style="color: 7D7D7D">Phone Number</label>
                                    <input type="text" class="form-control" name="no_telp" id="no_telp"
                                        value="{{ Auth::user()->no_telp }}" readonly />
                                </div>
                                <div class="mb-2">
                                    <label for="address" class="form-label" style="color: 7D7D7D">Address</label>
                                    <input type="text" class="form-control" name="alamat" id="alamat"
                                        value="{{ Auth::user()->alamat }}" readonly />
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <div class="mb-3">
                                    <label for="password" class="form-label" style="color: 7D7D7D">Password</label>
                                    <div class="position-relative mb-1">
                                        <input type="password" class="form-control" name="password" id="password"
                                            value="Enkripsi yaaaa" readonly />
                                        <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3 d-none"
                                            id="togglePassword" style="cursor: pointer;"></i>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="telegram" class="form-label" style="color: 7D7D7D">Link Telegram</label>
                                    <input type="text" class="form-control" name="link_tele" id="link_tele"
                                        value="{{ Auth::user()->link_tele }}" readonly />
                                </div>
                                <div class="mb-2">
                                    <label for="birth" class="form-label" style="color: 7D7D7D">Birth</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control pe-5" name="tgl_lahir" id="tgl_lahir"
                                            value="{{ Auth::user()->tgl_lahir?->format('d M Y') ?? '' }}" readonly />
                                        <i class="bi bi-calendar-event position-absolute top-50 end-0 translate-middle-y me-3"
                                            style="cursor: pointer;"></i>
                                    </div>
                                </div>

                                <!-- BUTTON Save & Cancel (muncul ketika edit mode) -->
                                <div>
                                    <div id="formActions" class="mt-3 d-none d-flex justify-content-end gap-2">
                                        <button type="button" id="cancelBtn"
                                            class="btn btn-cancel border-1 border-dark">Cancel</button>
                                        <button type="submit" class="btn btn-hitam">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="file" id="uploadImage" accept="image/*" class="form-control mb-3">
                    <div>
                        <img id="imagePreview" style="max-width: 100%; display: none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="cropButton">Crop & Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src=" {{ asset('build/js/main/profile.js') }}"></script>
@endsection