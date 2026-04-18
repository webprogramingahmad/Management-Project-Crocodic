@extends('layouts.app')

@section('title')
    Tasks
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/tasks.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* match project filter visuals and remove blue focus ring */
        .tasks-filter .dropdown-toggle:focus, .tasks-filter .dropdown-toggle:active {
            box-shadow: none !important;
        }

        /* Board = sisa viewport; toolbar diam; kolom kanban capped; hanya .overflow-scroll-container yang scroll */
        .tasks-board-page {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .tasks-board-page {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .tasks-board-toolbar {
            flex-shrink: 0;
        }

        .tasks-board-columns-wrap {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tasks-board-columns-inner {
            min-height: 0;
        }

        @media (min-width: 992px) {
            .tasks-board-columns-inner {
                height: 100%;
            }

            .tasks-board-columns-inner > .row.tasks-board-row {
                height: 100%;
                align-items: flex-start;
            }

            .tasks-board-col {
                display: flex;
                flex-direction: column;
                max-height: 100%;
            }
        }

        @media (max-width: 991.98px) {
            .tasks-board-col {
                display: flex;
                flex-direction: column;
            }
        }

        .tasks-board-page .task-card.tasks-board-card {
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
            width: 100%;
        }

        @media (max-width: 991.98px) {
            .tasks-board-page .task-card.tasks-board-card {
                max-height: min(70vh, 720px);
            }
        }

        .tasks-board-card-body {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .tasks-board-card-body .card-label {
            flex-shrink: 0;
        }

        .tasks-board-page .overflow-scroll-container {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endsection

@php
    $role = Auth::user()->role->role;
    $routeIndex = 'staff.tasks.index';
    if ($role === 'executive') {
        $routeIndex = 'executive.tasks.index';
    } elseif ($role === 'director') {
        $routeIndex = 'director.tasks.index';
    }
    $taskStatusUpdateUrlTemplate = match ($role) {
        'executive' => route('executive.task.updateStatus', ['id' => '__TASK_ID__']),
        'director' => route('director.task.updateStatus', ['id' => '__TASK_ID__']),
        default => route('staff.task.updateStatus', ['id' => '__TASK_ID__']),
    };
@endphp

@section('content')
    <div id="tasks-board" class="tasks-board-page">
        <div class="tasks-board-toolbar container-fluid gx-0 px-1 py-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route($routeIndex) }}" class="d-flex gap-2">
                        <div class="dropdown tasks-filter">
                            <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3 py-0" style="height: 35px; line-height:35px" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $projectId ? ($projects->firstWhere('id', $projectId)?->name ?? 'Project') : 'Project' }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('project_id') ? ('?' . http_build_query(request()->except('project_id'))) : '') }}">All Projects</a>
                                </li>
                                @foreach($projects as $project)
                                    <li>
                                        <a class="dropdown-item" href="{{ route($routeIndex) . (request()->except('project_id') ? ('?' . http_build_query(array_merge(request()->except('project_id'), ['project_id' => $project->id]))) : ('?project_id=' . $project->id)) }}">{{ $project->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div style="display:inline-block; position:relative; width:150px; height:35px;">
                            <input type="date" name="date" id="datepickerInput" value="{{ request('date') }}" style="position:absolute; inset:0; width:100%; height:100%; opacity:0; border:0; padding:0; margin:0; cursor:pointer;">
                            <button type="button" id="datepickerButton" class="d-flex justify-content-between btn btn-outline-secondary rounded-3 align-items-center gap-2 px-2 py-0" style="width: 150px; height:35px; line-height:35px; border-color: #6c757d;">
                                <span id="datepickerLabel">{{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('d/m/Y') : 'Date' }}</span>
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="d-flex gap-2">
                    @if ($role !== 'executive')
                        <button class="btn btn-dark rounded-3 px-4 py-0" style="height: 35px; line-height:35px" data-bs-toggle="modal" data-bs-target="#create-task"><i
                                class="bi bi-plus"></i> Create task</button>
                    @endif
                    @if ($role === 'director')
                        <button class="btn btn-dark rounded-3 px-4 py-0" style="height: 35px; line-height:35px" data-bs-toggle="modal" data-bs-target="#transfer-task">
                            <i class="bi bi-send-fill"></i> Transfer task
                        </button>
                    @endif
                </div>
            </div>
        </div>

    <script>
        (function () {
            var dateInput = document.getElementById('datepickerInput');
            var dateLabel = document.getElementById('datepickerLabel');
            if (!dateInput) return;

            dateInput.addEventListener('change', function () {
                if (this.value) {
                    var d = new Date(this.value);
                    var dd = String(d.getDate()).padStart(2, '0');
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var yyyy = d.getFullYear();
                    var formatted = dd + '/' + mm + '/' + yyyy;
                    dateLabel.textContent = formatted;
                } else {
                    dateLabel.textContent = 'Date';
                }
                var f = this.closest('form');
                if (f) f.submit();
            });
        })();
    </script>

        <div class="tasks-board-columns-wrap">
            <div class="tasks-board-columns-inner container-fluid py-1 px-1 gx-0">
            <div class="row g-1 g-md-2 g-lg-3 tasks-board-row">
                {{-- To do --}}
                <div class="col-12 col-md-6 col-lg-3 tasks-board-col">
                    <div class="task-card tasks-board-card bg-white rounded-3 shadow-sm">
                        <div class="task-header bg-danger rounded-top" style="height: 10px;"></div>
                        <div class="tasks-board-card-body p-3">
                            <h5 class="card-label mb-2">To do</h5>
                            <div class="overflow-scroll-container" data-status="{{ $statusTodo->id }}">
                                @foreach ($taskTodo as $task)
                                    <div class="border rounded-3 p-3 mb-2" data-id="{{ $task->id }}">
                                        @include('view.tasks.partials.tasks.card', ['task' => $task])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- In progress --}}
                <div class="col-12 col-md-6 col-lg-3 tasks-board-col">
                    <div class="task-card tasks-board-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #FFB42E;"></div>
                        <div class="tasks-board-card-body p-3">
                            <h5 class="card-label mb-2">In progress</h5>
                            <div class="overflow-scroll-container" data-status="{{ $statusProgress->id }}">
                                @foreach ($taskProgress as $task)
                                    <div class="border rounded-3 p-3 mb-2" data-id="{{ $task->id }}">
                                        @include('view.tasks.partials.tasks.card', ['task' => $task])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Review --}}
                <div class="col-12 col-md-6 col-lg-3 tasks-board-col">
                    <div class="task-card tasks-board-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #6FAEC9"></div>
                        <div class="tasks-board-card-body p-3">
                            <h5 class="card-label mb-2">Review</h5>
                            <div class="overflow-scroll-container" data-status="{{ $statusReview->id }}">
                                @foreach ($taskReview as $task)
                                    <div class="border rounded-3 p-3 mb-2" data-id="{{ $task->id }}">
                                        @include('view.tasks.partials.tasks.card', ['task' => $task])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Complate --}}
                <div class="col-12 col-md-6 col-lg-3 tasks-board-col">
                    <div class="task-card tasks-board-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #7DB546;"></div>
                        <div class="tasks-board-card-body p-3">
                            <h5 class="card-label mb-2">Complete</h5>
                            <div class="overflow-scroll-container" data-status="{{ $statusComplete->id }}">
                                @foreach ($taskComplete as $task)
                                    <div class="border rounded-3 p-3 mb-2" data-id="{{ $task->id }}">
                                        @include('view.tasks.partials.tasks.card', ['task' => $task])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <div class="modal fade" id="detail-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        @include('view.tasks.partials.tasks.modal-detail')
    </div>

    @if ($role !== 'executive')
        <div class="modal fade" id="create-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="createTaskModalLabel" aria-hidden="true">
            @include('view.tasks.partials.tasks.modal-create')
        </div>
    @endif

    @if ($role === 'director')
        <div class="modal fade" id="transfer-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="transferTaskModalLabel" aria-hidden="true">
            @include('view.tasks.partials.tasks.modal-transfer')
        </div>
    @endif

    <div class="modal fade" id="edit-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editTaskLabel" aria-hidden="true">
        @include('view.tasks.partials.tasks.modal-edit')
    </div>
@endpush

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.querySelectorAll(".overflow-scroll-container").forEach(column => {
            new Sortable(column, {
                group: "tasks",
                animation: 150,
                onAdd: function (evt) {
                    let taskId = evt.item.dataset.id;
                    let newStatus = evt.to.dataset.status;

                    let url = @json($taskStatusUpdateUrlTemplate).replace('__TASK_ID__', taskId);

                    fetch(url, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            id_status: newStatus
                        }),
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                alert("Gagal update status");
                                return;
                            }
                            if (typeof window.refreshTaskRunningTimerAfterStatus === "function") {
                                window.refreshTaskRunningTimerAfterStatus(
                                    evt.item,
                                    data.deadline_iso || null,
                                    !!data.show_timer
                                );
                            }
                        })
                        .catch(() => alert("Error update status"));
                }
            });
        });

        const projects = @json($projectsForTaskForms ?? $projects);

        const projectSelect = document.getElementById('projectSelect');
        const userSelect = document.getElementById('userSelect');

        if (projectSelect && userSelect) {
            projectSelect.addEventListener('change', function () {
                const projectId = this.value;
                const project = projects.find(p => p.id === projectId);

                userSelect.innerHTML = '<option value="" disabled selected>Select employee</option>';

                if (project) {
                    if (project.sdms) {
                        project.sdms.forEach(sdm => {
                            const opt = document.createElement('option');
                            opt.value = sdm.id;
                            opt.textContent = sdm.name;
                            userSelect.appendChild(opt);
                        });
                    }
                }
            });
        }
    </script>
    <!-- Using native date input to match create project UI -->
    <script src="{{ asset('build/js/main/tasks.js') }}"></script>
@endsection