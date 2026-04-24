@extends('layouts.app')

@section('title')
    {{ $project->name ?? 'Tasks' }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/tasks.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
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
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tasks-board-columns-inner {
            min-height: 0;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            container-type: inline-size;
            container-name: taskboard;
            --kb-gap: 0.5rem;
        }

        .tasks-board-row.tasks-board-row--kanban {
            flex-wrap: nowrap;
            gap: var(--kb-gap);
            min-width: 100%;
            width: max(
                100%,
                calc(5 * (100cqw - 3 * var(--kb-gap)) / 4 + 4 * var(--kb-gap))
            );
        }

        .tasks-board-col.tasks-board-col--kanban {
            flex: 0 0 calc((100cqw - 3 * var(--kb-gap)) / 4);
            width: calc((100cqw - 3 * var(--kb-gap)) / 4);
            min-width: calc((100cqw - 3 * var(--kb-gap)) / 4);
            max-width: calc((100cqw - 3 * var(--kb-gap)) / 4);
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
    $routeProjectIndex = 'executive.project.tasks.index';
    if ($role === 'director') {
        $routeProjectIndex = 'director.project.tasks.index';
    } elseif ($role === 'staff') {
        $routeProjectIndex = 'staff.project.tasks.index';
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
                    <form method="GET" action="{{ route($routeProjectIndex, $project->id) }}" class="d-flex gap-2">
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
                    @if (!empty($projectAllowsTaskCreation))
                        @if ($role !== 'executive')
                            <button class="btn btn-dark rounded-3 px-4 py-0" style="height: 35px; line-height:35px" data-bs-toggle="modal" data-bs-target="#create-taskproject"><i
                                    class="bi bi-plus"></i> Create task</button>
                        @endif
                        @if ($role === 'director')
                            <button class="btn btn-dark rounded-3 px-4 py-0" style="height: 35px; line-height:35px" data-bs-toggle="modal" data-bs-target="#transfer-taskproject">
                                <i class="bi bi-send-fill"></i> Transfer task
                            </button>
                        @endif
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
            <div class="row tasks-board-row tasks-board-row--kanban gx-0 pb-1 flex-nowrap mx-0">
                {{-- To do --}}
                <div class="col-auto tasks-board-col tasks-board-col--kanban">
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
                <div class="col-auto tasks-board-col tasks-board-col--kanban">
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
                <div class="col-auto tasks-board-col tasks-board-col--kanban">
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
                @if ($statusRevision)
                {{-- Revision --}}
                <div class="col-auto tasks-board-col tasks-board-col--kanban">
                    <div class="task-card tasks-board-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #C2410C;"></div>
                        <div class="tasks-board-card-body p-3">
                            <h5 class="card-label mb-2">Revision</h5>
                            <div class="overflow-scroll-container" data-status="{{ $statusRevision->id }}">
                                @foreach ($taskRevision as $task)
                                    <div class="border rounded-3 p-3 mb-2" data-id="{{ $task->id }}">
                                        @include('view.tasks.partials.tasks.card', ['task' => $task])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                {{-- Complete --}}
                <div class="col-auto tasks-board-col tasks-board-col--kanban">
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

    @if ($role === 'director')
        @include('view.tasks.partials.tasks.modal-review-decision')
    @endif

    @if (!empty($projectAllowsTaskCreation))
        @if ($role !== 'executive')
            <div class="modal fade" id="create-taskproject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="createTaskModalLabel" aria-hidden="true">
                @include('view.tasks.partials.projects.modal-create')
            </div>
        @endif

        @if ($role === 'director')
            <div class="modal fade" id="transfer-taskproject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="transferTaskModalLabel" aria-hidden="true">
                @include('view.tasks.partials.projects.modal-transfer')
            </div>
        @endif
    @endif

    <div class="modal fade" id="edit-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editTaskLabel" aria-hidden="true">
        @include('view.tasks.partials.projects.modal-edit')
    </div>
@endpush

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.__tasksBoardSortable = {
            role: @json($role),
            reviewId: @json($statusReview?->id),
            completeId: @json($statusComplete?->id),
            revisionId: @json($statusRevision?->id),
        };
        @if ($role === 'director')
        window.REVIEW_DECISION_URL_TEMPLATE = @json(route('director.task.reviewDecision', ['id' => '__TASK_ID__']));
        @endif
    </script>
    <script>
        (function () {
            var cfg = window.__tasksBoardSortable || {};
            if (cfg.role === "executive") {
                return;
            }
            document.querySelectorAll(".overflow-scroll-container").forEach(function (column) {
                new Sortable(column, {
                    group: "tasks",
                    animation: 150,
                    onMove: function (evt) {
                        var to = evt.to.getAttribute("data-status");
                        var from = evt.from.getAttribute("data-status");
                        if (cfg.revisionId && to === cfg.revisionId) {
                            return false;
                        }
                        if (cfg.reviewId && cfg.completeId && from === cfg.reviewId && to === cfg.completeId) {
                            return false;
                        }
                        return true;
                    },
                    onAdd: function (evt) {
                        var taskId = evt.item.dataset.id;
                        var newStatus = evt.to.dataset.status;
                        var url = @json($taskStatusUpdateUrlTemplate).replace('__TASK_ID__', taskId);
                        fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({ id_status: newStatus }),
                        })
                            .then(function (res) {
                                return res.json().then(function (data) {
                                    return { ok: res.ok, data: data };
                                });
                            })
                            .then(function (r) {
                                if (!r.ok || !r.data.success) {
                                    alert(r.data && r.data.message ? r.data.message : "Gagal update status");
                                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                                    return;
                                }
                                var data = r.data;
                                if (typeof window.refreshTaskRunningTimerAfterStatus === "function") {
                                    window.refreshTaskRunningTimerAfterStatus(
                                        evt.item,
                                        data.deadline_iso || null,
                                        !!data.show_timer,
                                        data.frozen_remain_ms != null ? data.frozen_remain_ms : null
                                    );
                                }
                            })
                            .catch(function () {
                                alert("Error update status");
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                            });
                    },
                });
            });
        })();
    </script>
    <!-- Using native date input to match create project UI -->
    <script src="{{ asset('build/js/main/tasks.js') }}"></script>
@endsection