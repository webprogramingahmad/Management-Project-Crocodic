@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/administrations.css') }}">
    <style>
        /* Same typography & padding as Action column (.btn.btn-sm.rounded-2); colors preserved */
        span.adm-status-pill {
            display: inline-block;
            pointer-events: none;
            cursor: default;
            min-width: 5rem;
            text-align: center;
            font-weight: 400;
            line-height: 1.5;
        }
        span.adm-status-pill.adm-status--accept {
            background-color: #7DB546 !important;
            color: #ffffff !important;
        }
        span.adm-status-pill.adm-status--reject {
            background-color: #EA4949 !important;
            color: #ffffff !important;
        }
        span.adm-status-pill.adm-status--pending {
            background-color: #FFB42E !important;
            color: #ffffff !important;
        }
        span.adm-status-pill.adm-status--default {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }

        /* Permission list: tinggi viewport; toolbar + thead tetap; hanya isi tabel scroll */
        .permission-list-page {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .permission-list-page {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .permission-list-card {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
        }

        .permission-list-toolbar {
            flex-shrink: 0;
        }

        .permission-list-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .permission-list-scroll .table {
            margin-bottom: 0;
        }

        .permission-list-thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
            vertical-align: middle;
            white-space: nowrap;
        }

        html[data-theme="dark"] .permission-list-thead th {
            background-color: var(--dash-bg-elevated, #2d2d32);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.35);
            color: var(--dash-text, #f4f4f5);
            border-color: var(--dash-border, #3f3f46);
        }
    </style>
@endsection

@php
    use App\Support\ProjectDuration;
    $role = Auth::user()->role->role;
@endphp

@section('content')
    <div class="permission-list-page">
    <div class="card permission-list-card gx-0 py-2 px-2 border"
        style="border-radius:15px; border-color:#E0E0E0CE !important;">
        <div class="permission-list-toolbar d-flex mt-4 mx-5 justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div class="fw-bold fs-2">
                Permission List
            </div>
            @if ($role === 'staff')
                <a href="{{ route('staff.administration.create') }}"
                    class="btn btn-dark rounded-3 d-flex align-items-center gap-2 px-3 py-0" style="height: 35px; line-height:35px">
                    <i class="bi bi-plus-lg"></i> Create Absent
                </a>
            @elseif ($role === 'director')
                <a href="{{ route('director.administration.create') }}"
                    class="btn btn-dark rounded-3 d-flex align-items-center gap-2 px-3 py-0" style="height: 35px; line-height:35px">
                    <i class="bi bi-plus-lg"></i> Create Absent
                </a>
            @endif
        </div>
        <div class="permission-list-scroll px-2 pb-2">
            <table class="table permission-list-table">
                <thead class="permission-list-thead fw-semibold text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 6%;" scope="col">#</th>
                        <th style="width: 12%;" scope="col">User</th>
                        <th class="text-center" style="width: 12%;" scope="col">Category</th>
                        <th class="text-center" style="width: 11%;" scope="col">Long leave</th>
                        <th class="text-center" style="width: 10%;" scope="col">Start Date</th>
                        <th class="text-center" style="width: 10%;" scope="col">End Date</th>
                        <th class="text-center" style="width: 8%;" scope="col">Bring Laptop</th>
                        <th class="text-center" style="width: 8%;" scope="col">Contacted</th>
                        <th class="text-center" style="width: 8%;" scope="col">Status</th>
                        <th class="text-center" style="width: 15%;" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($administrations as $absent)
                        <tr>
                            <td class="text-center fw-semibold" style="width: 6%;">{{ $loop->iteration }}</td>
                            <td style="width: 12%;" class="fw-semibold">{{ ucwords($absent->user->name) }}</td>
                            <td class="text-center fw-semibold" style="width: 12%;">{{ ucwords($absent->category->name) }}
                            </td>
                            <td class="text-center fw-semibold text-break" style="width: 11%;">
                                {{ ProjectDuration::label($absent->start_date?->format('Y-m-d'), $absent->end_date?->format('Y-m-d')) }}
                            </td>
                            <td class="text-center fw-semibold" style="width: 10%;">
                                {{ \Carbon\Carbon::parse($absent->start_date)->translatedFormat('d F Y') }}
                            </td>
                            <td class="text-center fw-semibold" style="width: 10%;">
                                {{ \Carbon\Carbon::parse($absent->end_date)->translatedFormat('d F Y') }}
                            </td>
                            <td class="text-center fw-semibold" style="width: 8%;">
                                {{ $absent->bring_laptop ? 'Yes' : 'No' }}
                            </td>
                            <td class="text-center fw-semibold" style="width: 8%;">
                                {{ $absent->contacted ? 'Yes' : 'No' }}
                            </td>
                            <td class="text-center" style="width: 8%;">
                                @php
                                    $statusKey = strtolower((string) $absent->status->name);
                                    $statusMod = in_array($statusKey, ['accept', 'reject', 'pending'], true)
                                        ? $statusKey
                                        : 'default';
                                @endphp
                                <span class="btn btn-sm rounded-2 border-0 adm-status-pill adm-status--{{ $statusMod }}">{{ ucfirst($absent->status->name) }}</span>
                            </td>
                            <td class="text-center" style="width: 15%;">
                                @if ($role === 'executive')
                                    <form action="{{ route('executive.administration.destroy', $absent->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin hapus project ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1 mb-lg-0 me-lg-1 rounded-2">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif

                                <a @if ($role === 'executive') href="{{ route('executive.administration.show', $absent->id) }}" @elseif ($role === 'director') href="{{ route('director.administration.show', $absent->id) }}"
                                @else href="{{ route('staff.administration.show', $absent->id) }}" @endif
                                    class="btn btn-sm btn-primary rounded-2">
                                    View Absent
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection