@extends('layouts.app')

@section('title')
    Activity
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/activity.css') }}">
    <style>
        .activity-card {
            display: flex;
            flex-direction: column;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .activity-card {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .activity-card .activity-filter-bar {
            flex-shrink: 0;
        }

        .activity-content-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .activity-content-scroll::-webkit-scrollbar {
            display: none;
        }

        .activity-card-link .card {
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            cursor: pointer;
        }

        .activity-card-link:hover .card,
        .activity-card-link:focus-visible .card {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            border-color: #6FAEC9 !important;
        }

        html[data-theme="dark"] .activity-card-link:hover .card,
        html[data-theme="dark"] .activity-card-link:focus-visible .card {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.45);
        }
    </style>
@endsection

@section('content')
    <div class="card activity-card gx-0 py-2 px-2" style="border-radius:15px; border-color:#E0E0E0CE">
        <div class="dropdown activity-filter-bar mt-2 mx-2">
            <form method="GET" class="d-flex align-items-center">
                <select name="month" class="form-select rounded-3 me-2 px-2 py-0" style="width: 150px; height: 35px; line-height:35px;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="form-select rounded-3 me-2 px-2 py-0 " style="width: 120px; height: 35px; line-height:35px;">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-dark rounded-3 px-3 py-0" style="height: 35px; line-height:35px">Filter</button>
            </form>
        </div>
        <div class="tab-container-fluid mb-2 activity-content-scroll">
            @if (($viewMode ?? 'team') === 'self')
                @if ($users->isEmpty())
                    <div class="empty-state d-flex align-items-center justify-content-center py-5">
                        <div class="text-muted">Data kinerja tidak ditemukan.</div>
                    </div>
                @else
                    @include('view.activity.partials.self', ['me' => $users->first()])
                @endif
            @elseif ($users->isEmpty())
                <div class="tab-container-fluid mb-2">
                    <div class="empty-state d-flex align-items-center justify-content-center">
                        <div class="d-block">
                            <div class="empty-icon mb-2 d-flex align-items-center justify-content-center">
                                <i class="bi bi-clipboard-x fs-1"></i>
                            </div>
                            <div class="empty-text">No employee yet</div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $activityShowRoute = Auth::user()->role?->role === 'director'
                        ? 'director.activity.show'
                        : 'executive.activity.show';
                @endphp
                <div class="row g-3 mt-1 mx-1">
                    @foreach ($users as $user)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <a href="{{ route($activityShowRoute, ['id' => $user->id, 'month' => $month, 'year' => $year]) }}"
                                class="activity-card-link text-decoration-none text-reset d-block h-100">
                                <div class="card border rounded-3 p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0D8ABC&color=fff' }}"
                                            class="rounded-circle me-3" width="60" height="60" alt="Avatar">
                                        <div>
                                            <h5 class="mb-0">{{ ucwords($user->name) }}</h5>
                                            <small class="text-muted">{{ ucwords($user->division?->divisi ?? ($user->role?->role ?? '-')) }}</small>
                                        </div>
                                    </div>
                                    <div class="row text-center mb-3">
                                        <div class="col-3">
                                            <div class="fw-semibold">Project</div>
                                            <div class="border rounded py-1">{{ $user->projects_joined_count }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-semibold">Tasks done</div>
                                            <div class="border rounded py-1">{{ $user->completed_task_count }}</div>
                                        </div>
                                        <div class="col-5 border-start">
                                            <div class="fw-semibold">Leave</div>
                                            <div class="border rounded py-1">{{ $user->accepted_absent_count }}</div>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="fw-semibold">Time Performance</div>
                                            <span class="fw-semibold">{{ $user->time_performance }}%</span>
                                        </div>
                                        <div class="progress position-relative w-100"
                                            style="height: 12px; border-radius: 20px; overflow: hidden;">
                                            <div class="progress-bar bg-dark"
                                                style="width: {{ $user->time_performance }}%; border-radius: 20px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('storage/js/main/activity.js') }}"></script>
@endsection