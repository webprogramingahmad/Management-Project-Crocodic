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
    </style>
@endsection

@section('content')
    <div class="admin-index-page">
    <div class="card admin-index-card gx-0 py-2 px-2 border"
        style="border-radius:15px; border-color:#E0E0E0CE !important;">
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
                            class="d-flex justify-content-between align-items-center btn btn-white border-secondary rounded-3 dropdown-toggle"
                            style="width: 135px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedProject" class="text-truncate">
                                {{ $selectedDivision?->divisi ?? 'Filter by divisi' }}
                            </span>
                            <i id="dropdownIcon" class=" ms-2"></i>
                        </button>
                        <ul class="dropdown-menu" id="projectDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('executive.accounts.index') }}">
                                    All Divisi
                                </a>
                            </li>
                            @foreach ($divisions as $division)
                                <li>
                                    <a class="dropdown-item"
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
                        Add New SDM
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
                                        class="btn btn-primary rounded mb-1 mb-lg-0 me-lg-1">
                                        <i class="bi bi-eye"></i>
                                        View
                                    </a>
                                    <form action="{{ route('executive.accounts.destroy', $user->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus user ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger rounded">
                                            <i class="bi bi-trash"></i>Delete
                                        </button>
                                    </form>
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