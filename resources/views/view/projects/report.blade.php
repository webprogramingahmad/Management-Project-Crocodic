@extends('layouts.app')

@section('title')
    Project Report — {{ ucwords($project->name) }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/project.css') }}">
    <style>
        .project-report-page {
            display: flex;
            flex-direction: column;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .project-report-page {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .project-report-toolbar {
            flex-shrink: 0;
        }

        .project-report-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .project-report-scroll::-webkit-scrollbar {
            display: none;
        }

        .report-kpi-card {
            text-align: center;
        }

        .report-kpi-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .report-task-link {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        .report-task-link:hover {
            color: #6FAEC9;
            text-decoration: underline;
        }

        html[data-theme="dark"] .report-task-link:hover {
            color: #8ec5dc;
        }

        span.proj-pill {
            display: inline-block;
            pointer-events: none;
            min-width: 5rem;
            text-align: center;
            font-weight: 400;
            line-height: 1.5;
            text-transform: capitalize;
        }

        span.proj-pill.proj-level--low { background-color: #6FAEC9 !important; color: #fff !important; }
        span.proj-pill.proj-level--medium { background-color: #FFB42E !important; color: #fff !important; }
        span.proj-pill.proj-level--high { background-color: #EA4949 !important; color: #fff !important; }
        span.proj-pill.proj-level--default { background-color: #6c757d !important; color: #fff !important; }
        span.proj-pill.proj-status--maintenance { background-color: #E0E0E0 !important; color: #000 !important; }
        span.proj-pill.proj-status--todo { background-color: #EA4949 !important; color: #fff !important; }
        span.proj-pill.proj-status--running { background-color: #038C8C !important; color: #fff !important; }
        span.proj-pill.proj-status--complete { background-color: #7DB546 !important; color: #fff !important; }
        span.proj-pill.proj-status--default { background-color: #6c757d !important; color: #fff !important; }
    </style>
@endsection

@section('content')
    @php
        $lvl = strtolower((string) ($project->difficulty?->difficulty ?? 'default'));
        if (! in_array($lvl, ['low', 'medium', 'high'], true)) {
            $lvl = 'default';
        }
        $rawStatus = strtolower((string) ($project->status?->status ?? ''));
        if (str_contains($rawStatus, 'maintenance')) {
            $sclass = 'maintenance';
        } elseif (str_contains($rawStatus, 'not') || str_contains($rawStatus, 'to do') || str_contains($rawStatus, 'todo')) {
            $sclass = 'todo';
        } elseif (str_contains($rawStatus, 'running')) {
            $sclass = 'running';
        } elseif (str_contains($rawStatus, 'complete') || str_contains($rawStatus, 'finish')) {
            $sclass = 'complete';
        } else {
            $sclass = 'default';
        }
    @endphp

    <div class="card project-report-page gx-0 py-2 px-2 border" style="border-radius:15px; border-color:#E0E0E0CE">
        <div class="project-report-toolbar d-flex align-items-center justify-content-between gap-2 flex-wrap mt-2 mx-2 mb-2">
            <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary rounded-3 px-3 py-0 d-inline-flex align-items-center gap-2"
                style="height: 35px; line-height:35px;">
                <i class="bi bi-arrow-left"></i> Back to Projects
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route($reportPdfRoute, $project->id) }}"
                    class="btn btn-dark rounded-3 px-3 py-0 d-inline-flex align-items-center gap-2"
                    style="height: 35px; line-height:35px;">
                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                </a>
                <span class="text-muted small fw-semibold">Project Report</span>
            </div>
        </div>

        <div class="project-report-scroll px-2 pb-3">
            {{-- Header --}}
            <div class="card border rounded-3 p-3 p-md-4 mb-3 mx-1">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h4 class="fw-bold mb-2">{{ ucwords($project->name) }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if ($project->difficulty)
                                <span class="btn btn-sm rounded-2 border-0 proj-pill proj-level--{{ $lvl }}">{{ $project->difficulty->difficulty }}</span>
                            @endif
                            @if ($project->status)
                                <span class="btn btn-sm rounded-2 border-0 proj-pill proj-status--{{ $sclass }}">{{ $project->status->status }}</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-person-badge me-1"></i>Director: {{ ucwords($project->director?->name ?? '-') }}
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ $project->start_date?->translatedFormat('d F Y') }}
                            &ndash; {{ $project->end_date?->translatedFormat('d F Y') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold mb-1">Completion</div>
                        <div class="fs-4 fw-bold">{{ $progressPercent }}%</div>
                        <small class="text-muted">{{ $completedCount }} / {{ $totalTasks }} tasks</small>
                    </div>
                </div>
                <div class="progress w-100" style="height: 10px; border-radius: 20px;">
                    <div class="progress-bar bg-success" style="width: {{ $progressPercent }}%; border-radius: 20px;"></div>
                </div>
            </div>

            {{-- KPI --}}
            <div class="row g-3 mb-3 mx-0">
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 p-3 report-kpi-card h-100">
                        <div class="text-muted small fw-semibold">Total Tasks</div>
                        <div class="report-kpi-value">{{ $totalTasks }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 p-3 report-kpi-card h-100">
                        <div class="text-muted small fw-semibold">Complete</div>
                        <div class="report-kpi-value text-success">{{ $completedCount }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 p-3 report-kpi-card h-100">
                        <div class="text-muted small fw-semibold">In Progress</div>
                        <div class="report-kpi-value">{{ $activeCount }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 p-3 report-kpi-card h-100">
                        <div class="text-muted small fw-semibold">To Do</div>
                        <div class="report-kpi-value">{{ $countByClass['todo'] }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border rounded-3 p-3 report-kpi-card h-100">
                        <div class="text-muted small fw-semibold">On-time Rate</div>
                        <div class="report-kpi-value">{{ $onTimePercent }}%</div>
                    </div>
                </div>
            </div>

            {{-- Team contribution --}}
            <div class="card border rounded-3 p-3 mb-3 mx-1">
                <h6 class="fw-bold mb-3"><i class="bi bi-people me-1"></i>Team Contribution</h6>
                @if ($memberStats->isEmpty())
                    <div class="text-muted small">Belum ada task pada project ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Member</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Complete</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-center">On time</th>
                                    <th class="text-center">Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($memberStats as $stat)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ ucwords($stat->user?->name ?? 'Unassigned') }}</div>
                                            <small class="text-muted">{{ ucwords($stat->user?->division?->divisi ?? ($stat->user?->role?->role ?? '-')) }}</small>
                                        </td>
                                        <td class="text-center fw-semibold">{{ $stat->total }}</td>
                                        <td class="text-center">{{ $stat->completed }}</td>
                                        <td class="text-center">{{ $stat->active }}</td>
                                        <td class="text-center text-success">{{ $stat->on_time }}</td>
                                        <td class="text-center text-danger">{{ $stat->late }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Task list --}}
            <div class="card border rounded-3 p-3 mx-1">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-1"></i>All Tasks</h6>
                @if ($tasks->isEmpty())
                    <div class="text-muted small">Belum ada task.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Task</th>
                                    <th>Assignee</th>
                                    <th>Created by</th>
                                    <th>Status</th>
                                    <th>Level</th>
                                    <th class="text-center">On time</th>
                                    <th class="text-center">Evidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    @php
                                        $timing = \App\Support\TaskRunningTimer::reviewedOnTime($task);
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route($reportTaskRoute, ['id' => $project->id, 'taskId' => $task->id]) }}"
                                                class="report-task-link">
                                                {{ ucwords($task->name) }}
                                            </a>
                                        </td>
                                        <td>{{ ucwords($task->user?->name ?? '-') }}</td>
                                        <td>{{ ucwords($task->creator?->name ?? '-') }}</td>
                                        <td><span class="badge bg-secondary rounded-pill">{{ $task->status?->status ?? '-' }}</span></td>
                                        <td>{{ $task->difficulty?->difficulty ?? '-' }}</td>
                                        <td class="text-center">
                                            @if ($timing === true)
                                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                            @elseif ($timing === false)
                                                <span class="text-danger"><i class="bi bi-x-circle-fill"></i></span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $task->photos_count > 0 ? $task->photos_count : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
