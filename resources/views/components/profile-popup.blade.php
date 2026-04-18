{{-- Popup profil: hover + klik buka, mouseleave tutup area host; toggle tema global --}}
@php
    $avatarPx = $avatarPx ?? 48;
    $popupUid = $popupUid ?? 'pp_' . $avatarPx;
@endphp

<div class="profile-popup-host" data-profile-popup data-popup-uid="{{ $popupUid }}">
    <button type="button" class="btn p-0 border-0 rounded-circle profile-trigger" aria-expanded="false"
        aria-haspopup="true" aria-label="Menu profil">
        @if ($user->avatar)
            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" class="rounded-circle" alt=""
                style="width: {{ $avatarPx }}px; height: {{ $avatarPx }}px; object-fit: cover;">
        @else
            <div class="rounded-circle d-flex align-items-center justify-content-center profile-popup-avatar-fallback"
                style="width: {{ $avatarPx }}px; height: {{ $avatarPx }}px; font-size: 22px; font-weight: bold;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif
    </button>
    <div class="profile-popup-panel shadow" role="menu">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="fw-bold profile-name">{{ Str::ucfirst($user->name) }}</div>
        </div>
        <div class="profile-email mb-3">{{ $user->email }}</div>

        <div class="d-flex align-items-center justify-content-between mb-3 profile-theme-row">
            <span class="profile-theme-heading mb-0">Theme</span>
            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                <input class="form-check-input profile-theme-toggle" type="checkbox" role="switch"
                    id="profileThemeToggle_{{ $popupUid }}" data-theme-toggle
                    aria-label="Aktifkan tema gelap">
                <label class="form-check-label profile-theme-side-label mb-0" for="profileThemeToggle_{{ $popupUid }}">
                    <i class="bi bi-sun-fill profile-theme-icon theme-icon-light" aria-hidden="true"></i>
                    <i class="bi bi-moon-stars-fill profile-theme-icon theme-icon-dark" aria-hidden="true"></i>
                </label>
            </div>
        </div>

        <a href="{{ $profileRoute }}" class="btn btn-secondary w-100 mb-2 profile-action profile-btn-edit">Profile</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 profile-action profile-btn-logout">Logout</button>
        </form>
    </div>
</div>
