@extends('layouts.app')

@section('title')
    Projects
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/project.css') }}">
    <style>
        /* Same capsule shape & typography as Administration (.adm-status-pill + .btn.btn-sm.rounded-2) */
        span.proj-pill {
            display: inline-block;
            pointer-events: none;
            cursor: default;
            min-width: 5rem;
            text-align: center;
            font-weight: 400;
            line-height: 1.5;
            text-transform: capitalize;
        }
        span.proj-pill.proj-level--low {
            background-color: #6FAEC9 !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-level--medium {
            background-color: #FFB42E !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-level--high {
            background-color: #EA4949 !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-level--default {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-status--maintenance {
            background-color: #E0E0E0 !important;
            color: #000000 !important;
        }
        span.proj-pill.proj-status--todo {
            background-color: #EA4949 !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-status--running {
            background-color: #038C8C !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-status--complete {
            background-color: #7DB546 !important;
            color: #ffffff !important;
        }
        span.proj-pill.proj-status--default {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }
        /* Action column: icon + label vertically centered, same as Administration row actions */
        .btn-proj-action {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            vertical-align: middle;
            line-height: 1.25;
        }
        .btn-proj-action i {
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
        /* Edit capsule: #FFB42E background, white label/icon */
        .btn-proj-edit,
        .btn-proj-edit i {
            color: #ffffff !important;
        }
        .btn-proj-edit {
            background-color: #FFB42E !important;
            border-color: #FFB42E !important;
        }
        .btn-proj-edit:hover,
        .btn-proj-edit:focus {
            background-color: #eca82a !important;
            border-color: #eca82a !important;
            color: #ffffff !important;
        }
        .btn-proj-edit:hover i,
        .btn-proj-edit:focus i {
            color: #ffffff !important;
        }

        /* Sama seperti Permission / Admin: viewport height, toolbar + thead tetap, isi tabel scroll */
        .project-list-page {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .project-list-page {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .project-list-card {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
        }

        .project-list-toolbar {
            flex-shrink: 0;
        }

        .project-index-table-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .project-index-table-scroll .table {
            margin-bottom: 0;
        }

        .project-list-thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
            vertical-align: middle;
            white-space: nowrap;
        }

        html[data-theme="dark"] .project-list-thead th {
            background-color: var(--dash-bg-elevated, #2d2d32);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.35);
            color: var(--dash-text, #f4f4f5);
            border-color: var(--dash-border, #3f3f46);
        }
    </style>
@endsection

@php
    $role = Auth::user()->role->role;
    $routeIndex = 'staff.projects.index';
    if ($role === 'executive') {
        $routeIndex = 'executive.projects.index';
    } elseif ($role === 'director') {
        $routeIndex = 'director.projects.index';
    }
@endphp

@section('content')
    <div class="project-list-page">
    <div class="card project-list-card gx-0 px-1 py-1 border"
        style="border-radius:15px; border-color:#E0E0E0CE !important;">
        <div class="project-list-toolbar d-flex mt-4 mx-4 justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form class="d-flex align-items-center search border border-secondary rounded-3" method="GET" action="">
                    <button class="btn btn-link p-0 ms-2 text-secondary" type="submit" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <input class="form-control border-0 bg-transparent small" type="search" id="search" name="search"
                        autocomplete="off" placeholder="Search Project" aria-label="Search project" value="{{ request('search') }}" />
                </form>
                <!-- Filter by Level (low, medium, high) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3 py-0" style="height: 35px; line-height:35px" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Filter by Level
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('level') ? ('?' . http_build_query(request()->except('level'))) : '') }}">All Levels</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('level') ? ('?' . http_build_query(array_merge(request()->except('level'), ['level' => 'Low']))) : ('?level=Low')) }}">Low</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('level') ? ('?' . http_build_query(array_merge(request()->except('level'), ['level' => 'Medium']))) : ('?level=Medium')) }}">Medium</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('level') ? ('?' . http_build_query(array_merge(request()->except('level'), ['level' => 'High']))) : ('?level=High')) }}">High</a></li>
                    </ul>
                </div>

                <!-- Filter by Status -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3 py-0" style="height: 35px; line-height:35px" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Filter by Status
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('status') ? ('?' . http_build_query(request()->except('status'))) : '') }}">All Statuses</a>
                        </li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('status') ? ('?' . http_build_query(array_merge(request()->except('status'), ['status' => 'To Do']))) : ('?status=To+Do')) }}">To Do</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('status') ? ('?' . http_build_query(array_merge(request()->except('status'), ['status' => 'Running']))) : ('?status=Running')) }}">Running</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('status') ? ('?' . http_build_query(array_merge(request()->except('status'), ['status' => 'Maintenance']))) : ('?status=Maintenance')) }}">Maintenance</a></li>
                        <li><a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('status') ? ('?' . http_build_query(array_merge(request()->except('status'), ['status' => 'Completed']))) : ('?status=Completed')) }}">Completed</a></li>
                    </ul>
                </div>
            </div>
            @if ($role === 'executive')
                <a href="{{ route('executive.projects.create') }}" class="btn btn-dark rounded-3 d-flex align-items-center gap-2 px-3 py-0" style="height:35px; line-height:35px">
                    <i class="bi bi-plus-lg"></i> Create Project
                </a>
            @endif
        </div>
        <div class="project-index-table-scroll px-2 pb-2">
            <table class="table project-list-table">
                <thead class="project-list-thead fw-semibold text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 6%;" scope="col">#</th>
                        <th style="width: 20%;" scope="col">Project Name</th>
                        <th class="text-center" style="width: 12%;" scope="col">Start</th>
                        <th class="text-center" style="width: 12%;" scope="col">Deadline</th>
                        <th class="text-center" style="width: 14%;" scope="col">Director</th>
                        <th class="text-center" style="width: 8%;" scope="col">Level</th>
                        <th class="text-center" style="width: 8%;" scope="col">Status</th>
                        <th class="text-center" style="width: 18%;" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        @php
                            $timelineStartBalance = \App\Support\ProjectTimelineTimer::todoStartBalanceSeconds($project);
                            $timelineEndBalance = \App\Support\ProjectTimelineTimer::runningEndBalanceSeconds($project);
                            $startInValue = $timelineStartBalance !== null
                                ? \App\Support\ProjectTimelineTimer::formatBalanceDays($timelineStartBalance)
                                : null;
                            $startInLate = $timelineStartBalance !== null && $timelineStartBalance < 0;
                            $endsInValue = $timelineEndBalance !== null
                                ? \App\Support\ProjectTimelineTimer::formatBalanceDays($timelineEndBalance)
                                : null;
                            $endsInLate = $timelineEndBalance !== null && $timelineEndBalance < 0;
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold" style="width: 6%;">{{ $loop->iteration }}</td>
                            <td style="width: 20%;" class="fw-semibold">
                                <div>{{ ucwords($project->name) }}</div>
                            </td>
                            <td class="text-center fw-semibold" style="width: 12%;">
                                {{ \Carbon\Carbon::parse($project->start_date)->translatedFormat('d F Y') }}
                                @if ($startInValue)
                                    <div class="small mt-1 {{ $startInLate ? 'text-danger' : 'text-muted' }}">
                                        Start in: {{ $startInValue }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-semibold" style="width: 12%;">
                                {{ \Carbon\Carbon::parse($project->end_date)->translatedFormat('d F Y') }}
                                @if ($endsInValue)
                                    <div class="small mt-1 {{ $endsInLate ? 'text-danger' : 'text-muted' }}">
                                        Ends in: {{ $endsInValue }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-semibold" style="width: 14%;">{{ ucwords($project->director?->name) }}
                            </td>
                            <td class="text-center" style="width: 8%;">
                                @if ($project->difficulty)
                                    @php
                                        $lvl = strtolower($project->difficulty->difficulty);
                                        if (! in_array($lvl, ['low', 'medium', 'high'], true)) {
                                            $lvl = 'default';
                                        }
                                    @endphp
                                    <span class="btn btn-sm rounded-2 border-0 proj-pill proj-level--{{ $lvl }}">{{ $project->difficulty->difficulty }}</span>
                                @else
                                    <span class="fw-semibold"> - </span>
                                @endif
                            </td>
                            <td class="text-center" style="width: 8%;">
                                @if ($project->status)
                                    @php
                                        $raw = strtolower($project->status->status ?? '');
                                        if (str_contains($raw, 'maintenance')) {
                                            $sclass = 'maintenance';
                                        } elseif (str_contains($raw, 'not') || str_contains($raw, 'to do') || str_contains($raw, 'todo')) {
                                            $sclass = 'todo';
                                        } elseif (str_contains($raw, 'running')) {
                                            $sclass = 'running';
                                        } elseif (str_contains($raw, 'complete') || str_contains($raw, 'finish')) {
                                            $sclass = 'complete';
                                        } else {
                                            $sclass = 'default';
                                        }
                                    @endphp
                                    <span class="btn btn-sm rounded-2 border-0 proj-pill proj-status--{{ $sclass }}">{{ $project->status->status }}</span>
                                @else
                                    <span class="fw-semibold"> - </span>
                                @endif
                            </td>
                            <td class="text-center align-top" style="width: 18%;">
                                @if ($role === 'director' || $role === 'executive')
                                    <a @if ($role === 'executive') href="{{ route('executive.project.edit', $project->id) }}" @else
                                        href="{{ route('director.project.edit', $project->id) }}"
                                    @endif class="btn btn-sm btn-warning btn-proj-action btn-proj-edit mb-1 mb-lg-0 me-lg-1 rounded-2">
                                        <i class="bi bi-pencil"></i><span>Edit</span>
                                    </a>
                                @endif

                                @if ($role === 'executive')
                                    <form action="{{ route('executive.project.destroy', $project->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin hapus project ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-proj-action mb-1 mb-lg-0 me-lg-1 rounded-2">
                                            <i class="bi bi-trash"></i><span>Delete</span>
                                        </button>
                                    </form>
                                @endif

                                <a @if ($role === 'executive') href="{{ route('executive.project.tasks.index', $project->id) }}" @elseif ($role === 'director')
                                href="{{ route('director.project.tasks.index', $project->id) }}" @else
                                    href="{{ route('staff.project.tasks.index', $project->id) }}" @endif
                                    class="btn btn-sm btn-primary btn-proj-action rounded-2">
                                    <span>View Tasks</span>
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