@php
    $role = Auth::user()->role->role;
    $user = Auth::user();
    $profileRoute = match ($role) {
        'staff' => route('staff.profile.index'),
        'director' => route('director.profile.index'),
        default => route('executive.profile.index'),
    };
@endphp

<style>
    .logout-btn {
        background: #efefef;
        color: #7D7D7D;
        border: none;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background-color: #6FAEC9;
        color: #ffffff;
    }

    .logout-btn:focus {
        outline: none;
        box-shadow: none;
    }

    header {
        display: flex;
        align-items: center !important;
    }

    .header {
        display: flex;
        align-items: center !important;
    }

    .header-search-wrapper {
        display: flex;
        align-items: center !important;
        margin: 0px !important;
        padding: 3px !important;
    }

    .header-search-wrapper input {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .logout-btn {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
</style>

<!-- Header untuk layar kecil -->
<header class="d-flex d-lg-none align-items-center justify-content-between border-bottom border-2 border-gray-200 px-3 py-3 bg-white" style="height: 70px;">
    <div class="d-flex align-items-center gap-2 ps-4">
        @include('components.app-logo', ['variant' => 'mobile'])
    </div>

  <!--  <div class="d-flex align-items-center justify-content-center flex-grow-1 mx-3">
        <form action="{{ route('search.route') }}" method="GET"
            class="d-flex align-items-center w-50 max-w-md bg-light border rounded-pill shadow-sm px-3 py-1 header-search-wrapper" style="max-width: 550px; width: 100%;">
            <input name="query" class="form-control border-0 bg-transparent small" type="search" placeholder="Search project"
                aria-label="Search project" />
            <button type="submit" class="btn btn-link p-0 ms-2 header-search-btn">
                <i class="fas fa-search" style="color:#038C8C !important;"></i>
            </button>
        </form>
    </div> -->

    <div class="header d-flex align-items-center gap-3 pe-2">
        <div class="text-end">
            <p class="fw-semibold m-0 header-user-name" style="font-size: 1.125rem;">{{ Str::ucfirst(Auth::user()->name) }}</p>
            @if ($role === 'staff')
                <p class="text-secondary m-0 header-user-role" style="font-size: 0.75rem;">
                    {{ Str::ucfirst(Auth::user()->division->divisi ?? '-') }}
                </p>
            @else
                <p class="text-secondary m-0 header-user-role" style="font-size: 0.75rem;">
                    {{ \App\Support\RoleDisplay::label(Auth::user()->role->role ?? null) }}
                </p>
            @endif
        </div>
        @include('components.profile-popup', ['avatarPx' => 47, 'popupUid' => 'mobile'])
    </div>
</header>

<!-- Header untuk layar besar -->
<header class="d-none d-lg-flex align-items-center justify-content-between border-bottom border-1 border-gray-200 px-4 py-3 bg-white" style="height: 80px;">
    <div class="d-flex align-items-center gap-2">
        @include('components.app-logo', ['variant' => 'desktop'])
    </div>
<!--    <form action="{{ route('search.route') }}" method="GET"
        class="d-flex align-items-center w-50 max-w-md bg-light border rounded-pill shadow-sm px-3 py-1 header-search-wrapper" style="max-width: 550px; width: 100%;">
        <input name="query" class="form-control border-0 bg-transparent small" type="search" placeholder="Search project"
            aria-label="Search project" />
        <button type="submit" class="btn btn-link p-0 ms-2 me-3 header-search-btn">
            <i class="fas fa-search" style="color:#038C8C !important;"></i>
        </button>
    </form> -->

    <div class="header d-flex align-items-center gap-3">
        <div class="text-end">
            <p class="fw-semibold m-0 header-user-name" style="font-size: 1rem;">{{ Str::ucfirst(Auth::user()->name) }}</p>
            @if ($role === 'staff')
                <p class="text-secondary m-0 header-user-role" style="font-size: 0.75rem;">
                    {{ Str::ucfirst(Auth::user()->division->divisi) }}
                </p>
            @else
                <p class="text-secondary m-0 header-user-role" style="font-size: 0.75rem;">
                    {{ \App\Support\RoleDisplay::label(Auth::user()->role->role ?? null) }}
                </p>
            @endif
        </div>
        @include('components.profile-popup', ['avatarPx' => 48, 'popupUid' => 'desktop'])
    </div>
</header>
