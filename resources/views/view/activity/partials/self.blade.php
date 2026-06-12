@php
    $periodLabel = \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') . ' ' . $year;
    $onTimeTasks = $me?->completed_ontime_tasks ?? collect();
    $lateTasks = $me?->completed_late_tasks ?? collect();
    $periodProjects = $me?->period_projects ?? collect();
@endphp

<div class="row g-3 mt-1 mx-1">
    {{-- Hero: identitas + Time Performance --}}
    <div class="col-12">
        <div class="card border rounded-3 p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-6 d-flex align-items-center">
                    <img src="{{ $me->avatar ? asset('storage/avatars/' . $me->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($me->name) . '&background=0D8ABC&color=fff' }}"
                        class="rounded-circle me-3" width="72" height="72" alt="Avatar">
                    <div>
                        <h4 class="mb-1">{{ ucwords($me->name) }}</h4>
                        <small class="text-muted">{{ ucwords($me->division?->divisi ?? ($me->role?->role ?? '-')) }}</small>
                        <div class="text-muted small mt-1"><i class="bi bi-calendar3 me-1"></i>{{ $periodLabel }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="fw-semibold">Time Performance</div>
                        <span class="fw-bold fs-5">{{ $me->time_performance }}%</span>
                    </div>
                    <div class="progress position-relative w-100" style="height: 14px; border-radius: 20px; overflow: hidden;">
                        <div class="progress-bar bg-dark" style="width: {{ $me->time_performance }}%; border-radius: 20px;"></div>
                    </div>
                    <small class="text-muted">{{ $me->time_performance_ontime }} dari {{ $me->time_performance_total }} task selesai tepat waktu</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik ringkas --}}
    <div class="col-12 col-sm-4">
        <div class="card border rounded-3 p-3 h-100 text-center">
            <div class="text-muted fw-semibold mb-1"><i class="bi bi-kanban me-1"></i>Projects</div>
            <div class="fs-3 fw-bold">{{ $me->projects_joined_count }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border rounded-3 p-3 h-100 text-center">
            <div class="text-muted fw-semibold mb-1"><i class="bi bi-check2-square me-1"></i>Tasks done</div>
            <div class="fs-3 fw-bold">{{ $me->completed_task_count }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border rounded-3 p-3 h-100 text-center">
            <div class="text-muted fw-semibold mb-1"><i class="bi bi-calendar-x me-1"></i>Leave</div>
            <div class="fs-3 fw-bold">{{ $me->accepted_absent_count }}</div>
        </div>
    </div>

    {{-- Daftar project yang dikerjakan --}}
    <div class="col-12">
        <div class="card border rounded-3 p-3 h-100">
            <div class="fw-semibold mb-2"><i class="bi bi-folder2-open me-1"></i>Project yang dikerjakan</div>
            @if ($periodProjects->isEmpty())
                <div class="text-muted small">Tidak ada project pada periode ini.</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($periodProjects as $project)
                        @php
                            $sclass = strtolower((string) ($project->status?->class ?? ''));
                            $badgeClass = match ($sclass) {
                                'running' => 'bg-success',
                                'maintenance' => 'bg-secondary',
                                'completed' => 'bg-primary',
                                'todo' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <li class="list-group-item bg-transparent px-0 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ ucwords($project->name) }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($project->start_date)->translatedFormat('d M Y') }}
                                    &ndash; {{ \Carbon\Carbon::parse($project->end_date)->translatedFormat('d M Y') }}
                                </small>
                            </div>
                            <span class="badge {{ $badgeClass }} rounded-pill">{{ $project->status?->status ?? '-' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Task yang sudah dikerjakan: tepat waktu vs telat --}}
    <div class="col-12 col-lg-6">
        <div class="card border rounded-3 p-3 h-100">
            <div class="fw-semibold mb-2 text-success">
                <i class="bi bi-check-circle me-1"></i>Selesai tepat waktu ({{ $onTimeTasks->count() }})
            </div>
            @if ($onTimeTasks->isEmpty())
                <div class="text-muted small">Belum ada task selesai tepat waktu pada periode ini.</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($onTimeTasks as $task)
                        <li class="list-group-item bg-transparent px-0">
                            <div class="fw-semibold">{{ ucwords($task->name) }}</div>
                            <small class="text-muted">{{ ucwords($task->project?->name ?? 'Stand By') }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border rounded-3 p-3 h-100">
            <div class="fw-semibold mb-2 text-danger">
                <i class="bi bi-exclamation-circle me-1"></i>Selesai telat ({{ $lateTasks->count() }})
            </div>
            @if ($lateTasks->isEmpty())
                <div class="text-muted small">Tidak ada task yang telat pada periode ini.</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($lateTasks as $task)
                        <li class="list-group-item bg-transparent px-0">
                            <div class="fw-semibold">{{ ucwords($task->name) }}</div>
                            <small class="text-muted">{{ ucwords($task->project?->name ?? 'Stand By') }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
