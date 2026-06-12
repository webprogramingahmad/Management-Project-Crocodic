@extends('layouts.app')

@section('title')
    Accounts
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/admin.css') }}">
    <style>
        /*
         * Tinggi = sisa viewport (header + padding main py-3). Halaman tidak memanjang;
         * hanya .admin-index-table-scroll yang bergulir.
         */
        .admin-index-page {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .admin-index-page {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        /* Kartu mengisi penuh penampung; tidak mengikuti tinggi isi tabel */
        .admin-index-card {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
        }

        .admin-index-toolbar {
            flex-shrink: 0;
            position: relative;
            z-index: 10;
            overflow: visible;
        }

        .admin-index-toolbar .dropdown-menu {
            z-index: 1080;
        }

        .admin-index-toolbar #dropdownProjectBtn {
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }

        html[data-theme="light"] .admin-index-toolbar #dropdownProjectBtn:hover,
        html[data-theme="light"] .admin-index-toolbar #dropdownProjectBtn:focus,
        html[data-theme="light"] .admin-index-toolbar #dropdownProjectBtn.show {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
            box-shadow: none;
        }

        html[data-theme="light"] .admin-index-toolbar #projectDropdown .dropdown-item {
            transition: background-color .12s ease, color .12s ease;
        }

        html[data-theme="light"] .admin-index-toolbar #projectDropdown .dropdown-item:hover,
        html[data-theme="light"] .admin-index-toolbar #projectDropdown .dropdown-item:focus {
            background-color: #e9ecef;
            color: #212529;
        }

        html[data-theme="light"] .admin-index-toolbar #projectDropdown .dropdown-item.active {
            background-color: #dee2e6;
            color: #212529;
            font-weight: 600;
        }

        .admin-index-outer-card {
            border-radius: 15px !important;
            border-color: rgba(224, 224, 224, 0.7) !important;
        }

        .admin-index-table-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .admin-index-table-scroll .table {
            margin-bottom: 0;
        }

        .admin-index-thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
            vertical-align: middle;
        }

        html[data-theme="dark"] .admin-index-thead th {
            background-color: var(--dash-bg-elevated, #2d2d32);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.35);
            color: var(--dash-text, #f4f4f5);
            border-color: var(--dash-border, #3f3f46);
        }

        html[data-theme="dark"] .admin-index-outer-card {
            border-color: rgba(161, 161, 170, 0.35) !important;
        }
    </style>
@endsection

@section('content')
    <div class="admin-index-page">
    <div class="card admin-index-card admin-index-outer-card gx-0 py-2 px-2 border">
        <div class="admin-index-toolbar container-fluid py-4 px-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <form class="d-flex align-items-center search border border-secondary rounded-3" method="GET" action="">
                        <button class="btn btn-link p-0 ms-2 text-secondary" type="submit" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <input class="form-control border-0 bg-transparent small" type="search" id="search" name="search"
                            autocomplete="off" placeholder="Search staff" aria-label="Search staff" />
                    </form>
                    <div class="dropdown">
                        <button id="dropdownProjectBtn"
                            class="d-flex justify-content-between align-items-center btn btn-outline-secondary rounded-3 dropdown-toggle"
                            style="width: 135px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedProject" class="text-truncate">
                                {{ $selectedDivision?->divisi ?? 'Filter by division' }}
                            </span>
                            <i id="dropdownIcon" class=" ms-2"></i>
                        </button>
                        <ul class="dropdown-menu" id="projectDropdown">
                            <li>
                                <a class="dropdown-item {{ !$selectedDivision ? 'active' : '' }}" href="{{ route('executive.accounts.index') }}">
                                    All Divisions
                                </a>
                            </li>
                            @foreach ($divisions as $division)
                                <li>
                                    <a class="dropdown-item {{ $selectedDivision && (string) $selectedDivision->id === (string) $division->id ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['division_id' => $division->id]) }}">
                                        {{ Str::ucfirst($division->divisi) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('executive.accounts.create') }}" class="btn btn-dark rounded"><i class="bi bi-plus"></i>
                        Add New Account
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-index-table-scroll px-2 pb-2">
            <table class="table admin-index-table">
                <thead class="admin-index-thead fw-semibold text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 8%;" scope="col">#</th>
                        <th style="width: 23%;" scope="col">Username</th>
                        <th style="width: 12%;" scope="col">Divisi</th>
                        <th style="width: 23%;" scope="col">Email</th>
                        <th style="width: 12%;" scope="col">Role</th>
                        <th class="text-center" style="width: 22%;" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentUserId = (string) auth()->id();
                    @endphp
                    @foreach ($users as $user)
                        <tr>
                            <td class="text-center fw-semibold" style="width: 8%;">{{ $loop->iteration }}</td>
                            <td class="fw-semibold" style="width: 23%;">
                                <a href="{{ route('executive.accounts.show', $user->id) }}"
                                    class="profile-link">{{ Str::ucfirst($user->name) }}</a>
                            </td>
                            <td class="fw-semibold" style="width: 12%;">
                                {{ Str::ucfirst($user->division?->divisi ?? '-') }}
                            </td>
                            <td class="fw-semibold" style="width: 23%;">{{ $user->email }}</td>
                            <td class="fw-semibold" style="width: 12%;">{{ \App\Support\RoleDisplay::label($user->role->role ?? null) }}
                            </td>
                            <td class="fw-semibold" style="width: 22%;">
                                <div class="d-flex justify-content-center align-items-center">
                                    <a href="{{ route('executive.accounts.show', $user->id) }}"
                                        class="btn btn-primary rounded-2 mb-1 mb-lg-0 me-lg-1 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-eye"></i><span>View</span>
                                    </a>
                                    @if ((string) $user->id === $currentUserId)
                                        <button type="button" class="btn btn-secondary rounded-2 d-inline-flex align-items-center gap-1" disabled title="You cannot delete your own account">
                                            <i class="bi bi-trash"></i><span>Delete</span>
                                        </button>
                                    @else
                                        <form action="{{ route('executive.accounts.destroy', $user->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this account?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger rounded-2 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-trash"></i><span>Delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('build/js/main/admin.js') }}"></script>
@endsection