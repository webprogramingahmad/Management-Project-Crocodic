@extends('layouts.app')

@section('title')
    Tasks
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('storage/css/main/tasks.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .tasks-filter .dropdown-toggle:focus, .tasks-filter .dropdown-toggle:active {
            box-shadow: none !important;
        }
    </style>
@endsection

@section('content')
    <div id="tasks-board">
        <div class="container-fluid py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <div class="dropdown tasks-filter">
                        <button id="dropdownProjectBtn"
                            class="d-flex justify-content-between align-items-center btn btn-white border rounded dropdown-toggle"
                            style="width: 300px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedProject" class="text-truncate">Project</span>
                            <i id="dropdownIcon" class="bi bi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu" id="projectDropdown">
                            <li><a class="dropdown-item" href="#">Website Management Company</a></li>
                            <li><a class="dropdown-item" href="#">TokoKu Online Marketplace Application</a></li>
                            <li><a class="dropdown-item" href="#">CafeLink Menu Transaction Via Website</a></li>
                            <li><a class="dropdown-item" href="#">SiteCraft Website That Bring Your Business to Life</a>
                            </li>
                            <li><a class="dropdown-item" href="#">VirtuSphere Digital Innovation Without Borders</a></li>
                        </ul>
                    </div>
                    <div id="datepicker-wrapper" style="display:inline-block; position:relative; width:150px; height:35px;">
                        <input type="date" id="datepickerInput" name="date" value="{{ request('date') }}" style="position:absolute; inset:0; width:100%; height:100%; opacity:0; border:0; padding:0; margin:0; pointer-events: none;">
                        <button type="button" id="datepickerButton" class="d-flex justify-content-between btn btn-white border rounded-3 align-items-center gap-2 px-2 py-0" style="width: 150px; height:35px; line-height:35px">
                            <span id="datepickerLabel">{{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('d/m/Y') : 'Date' }}</span>
                            <i class="bi bi-calendar3"></i>
                        </button>
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
                                var params = new URLSearchParams(window.location.search);
                                if (this.value) params.set('date', this.value); else params.delete('date');
                                window.location.href='{{ route('staff.tasks.index') }}?' + params.toString();
                            });

                            var btn = document.getElementById('datepickerButton');
                            if (btn) btn.addEventListener('click', function () { if (typeof dateInput.showPicker === 'function') return dateInput.showPicker(); dateInput.focus(); dateInput.click(); });
                        })();
                    </script>
                    <div class="d-flex gap-2">
                    <button class="btn btn-dark rounded" data-bs-toggle="modal" data-bs-target="#create-task"><i
                            class="bi bi-plus"></i> Create task</button>
                    <button class="btn btn-dark rounded" data-bs-toggle="modal" data-bs-target="#transfer-task">
                        <i class="bi bi-send-fill"></i> Transfer task
                    </button>
                    <button class="btn btn-dark rounded" data-target="#all-task">View All Task</button>
                </div>

            </div>
        </div>

        <div class="container-fluid py-2">
            <div class="row g-1 g-md-2">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="task-card bg-white rounded-3 shadow-sm" style="max-height: 760px;">
                        <div class="task-header bg-danger rounded-top" style="height: 10px;"></div>
                        <div class="p-3">
                            <h5 class="card-label">To do</h5>
                            <div class="overflow-scroll-container">
                                <div class="border rounded-3 p-3 mb-2">
                                    <a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-1">Wordpress plugin update</h6>
                                            <span class="badge bg-danger">High</span>
                                        </div>
                                        <p class="mb-2 text-muted small">Website Management Company</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center text-muted small gap-1">
                                                <i class="bi bi-calendar3"></i>
                                                <span>Nov 4, 2024</span>
                                            </div>
                                            <img src="https://i.ibb.co/QmCyfkC/avatar.png" alt="User" class="rounded-circle"
                                                style="width: 30px; height: 30px;">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="task-card bg-white rounded-3 shadow-sm" style="max-height: 760px;">
                        <div class="task-header rounded-top" style="height: 10px; background: #FFB42E;"></div>
                        <div class="p-3">
                            <h5 class="card-label">In progress</h5>
                            <div class="overflow-scroll-container">
                                <div class="border rounded-3 p-3 mb-2">
                                    <a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-1">Wordpress plugin update</h6>
                                            <span class="badge bg-danger">High</span>
                                        </div>
                                        <p class="mb-2 text-muted small">Website Management Company</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center text-muted small gap-1">
                                                <i class="bi bi-calendar3"></i>
                                                <span>Nov 4, 2024</span>
                                            </div>
                                            <img src="https://i.ibb.co/QmCyfkC/avatar.png" alt="User" class="rounded-circle"
                                                style="width: 30px; height: 30px;">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="task-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #6FAEC9"></div>
                        <div class="p-3">
                            <h5 class="card-label">Review</h5>
                            <div class="overflow-scroll-container">
                                <div class="border rounded-3 p-3 mb-2">
                                    <a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-1">Wordpress plugin update</h6>
                                            <span class="badge bg-danger">High</span>
                                        </div>
                                        <p class="mb-2 text-muted small">Website Management Company</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center text-muted small gap-1">
                                                <i class="bi bi-calendar3"></i>
                                                <span>Nov 4, 2024</span>
                                            </div>
                                            <img src="https://i.ibb.co/QmCyfkC/avatar.png" alt="User" class="rounded-circle"
                                                style="width: 30px; height: 30px;">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="task-card bg-white rounded-3 shadow-sm">
                        <div class="task-header rounded-top" style="height: 10px; background: #7DB546;"></div>
                        <div class="p-3">
                            <h5 class="card-label">Complete</h5>
                            <div class="overflow-scroll-container">
                                <div class="border rounded-3 p-3 mb-2">
                                    <a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-1">Wordpress plugin update</h6>
                                            <span class="badge bg-danger">High</span>
                                        </div>
                                        <p class="mb-2 text-muted small">Website Management Company</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center text-muted small gap-1">
                                                <i class="bi bi-calendar3"></i>
                                                <span>Nov 4, 2024</span>
                                            </div>
                                            <img src="https://i.ibb.co/QmCyfkC/avatar.png" alt="User" class="rounded-circle"
                                                style="width: 30px; height: 30px;">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="all-task" class="d-none">
        <div class="card m-3" style="height: 800px;">
            <div class="d-flex mt-4 mx-5 justify-content-between align-items-center mb-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-sliders me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">All</a></li>
                        <li><a class="dropdown-item" href="#">In Progress</a></li>
                        <li><a class="dropdown-item" href="#">Completed</a></li>
                        <li><a class="dropdown-item" href="#">Delayed</a></li>
                    </ul>
                </div>
                <button class="btn btn-dark rounded d-flex align-items-center gap-2" data-target="#tasks-board">
                    Tasks Board View
                </button>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead class="fw-semibold" style="height: 30px;">
                        <tr>
                            <th class="text-center" style="width: 5%;" scope="col-1">#</th>
                            <th style="width: 25%;" scope="col">Task Name</th>
                            <th style="width: 26%;" scope="col">Project</th>
                            <th style="width: 16%;" scope="col">Assigned Employee</th>
                            <th class="text-center" style="width: 7%;" scope="col">Task Level</th>
                            <th class="text-center" style="width: 11%;" scope="col">Task Status</th>
                            <th class="text-center" style="width: 10%;" scope="col">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Wordpress Plugin Update</a></td>
                            <td class="fw-semibold" style="width: 26%;">Website Management</td>
                            <td class="fw-semibold" style="width: 16%;">Athena Cyntia</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Wordpress Plugin Update</a></td>
                            <td class="fw-semibold" style="width: 26%;">Website Management</td>
                            <td class="fw-semibold" style="width: 16%;">Athena Cyntia</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Wordpress Plugin Update</a></td>
                            <td class="fw-semibold" style="width: 26%;">Website Management</td>
                            <td class="fw-semibold" style="width: 16%;">Athena Cyntia</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Wordpress Plugin Update</a></td>
                            <td class="fw-semibold" style="width: 26%;">Website Management</td>
                            <td class="fw-semibold" style="width: 16%;">Athena Cyntia</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Wordpress Plugin Update</a></td>
                            <td class="fw-semibold" style="width: 26%;">Website Management</td>
                            <td class="fw-semibold" style="width: 16%;">Athena Cyntia</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                        <tr>
                            <td class="text-center fw-semibold" style="width: 5%;">1</td>
                            <td style="width: 25%;" class="fw-semibold"><a href="#" class="task-link" data-bs-toggle="modal"
                                    data-bs-target="#detail-task">Website Menu View</a></td>
                            <td class="fw-semibold" style="width: 26%;">CafeLink Menu Website</td>
                            <td class="fw-semibold" style="width: 16%;">Erika</td>
                            <td class="text-center" style="width: 7%;"><span class="badge bg-danger">High</span></td>
                            <td class="text-center" style="width: 11%;"><span class="badge bg-info">To do</span></td>
                            <td class="text-center fw-semibold" style="width: 10%;">Nov 4, 2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transfer-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="transferTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-transfer">
            <div class="modal-content">
                <div class="modal-header border-b-8 border-black pb-2">
                    <h5 class="modal-title fw-bold" id="transferTaskModalLabel">Transfer Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Task</label>
                            <input type="text" class="form-control" placeholder="Task name...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select class="form-select" name="project" required>
                                <option value="" selected disabled>Select project</option>
                                <option value="website-management">Website Management Company</option>
                                <option value="tokoku">TokoKu Online Marketplace Application</option>
                                <option value="cafelink">CafeLink Menu Transaction Via Website</option>
                                <option value="sitecraft">SiteCraft Website That Bring Your Business to Life</option>
                                <option value="virtusphere">VirtuSphere Digital Innovation Without Borders</option>
                            </select>

                        </div>
                        <div class="mb-3">
                            <label class="form-label">Send to</label>
                            <select class="form-select" name="send" required>
                                <option value="" disabled selected>Select employee</option>
                                <option value="adam">Adam Leviev</option>
                                <option value="seon">Seon Woo</option>
                                <option value="bill">Bill Gates</option>
                                <option value="lalisa">Lalisa Manaban</option>
                                <option value="athena">Athena Cyntia</option>
                            </select>
                        </div>
                        <div class="mb-2  d-flex align-items-center gap-3">
                            <label class="form-label d-block">Task Level</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="low" value="low">
                                <label class="form-check-label" for="low">Low</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="medium" value="medium">
                                <label class="form-check-label" for="medium">Medium</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="high" value="high">
                                <label class="form-check-label" for="high">High</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between bg-light px-3 py-2 rounded-3"
                            style="background-color: #F4F4F4;">
                            <p class="mb-0 text-danger small">Low: &gt; 2 hours</p>
                            <p class="mb-0 text-danger small">Medium: &gt; 6 hours</p>
                            <p class="mb-0 text-danger small">High: &lt; 6 hours</p>
                        </div>

                    </form>
                </div>

                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-submit" data-bs-dismiss="modal">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-create">
            <div class="modal-content">
                <div class="modal-header border-b-8 border-black pb-2">
                    <h5 class="modal-title fw-bold" id="transferTaskModalLabel">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Task</label>
                            <input type="text" class="form-control" placeholder="Task name...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project</label>
                            <select class="form-select" name="project" required>
                                <option value="" selected disabled>Select project</option>
                                <option value="website-management">Website Management Company</option>
                                <option value="tokoku">TokoKu Online Marketplace Application</option>
                                <option value="cafelink">CafeLink Menu Transaction Via Website</option>
                                <option value="sitecraft">SiteCraft Website That Bring Your Business to Life</option>
                                <option value="virtusphere">VirtuSphere Digital Innovation Without Borders</option>
                            </select>

                        </div>
                        <div class="mb-2  d-flex align-items-center gap-3">
                            <label class="form-label d-block">Task Level</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="low" value="low">
                                <label class="form-check-label" for="low">Low</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="medium" value="medium">
                                <label class="form-check-label" for="medium">Medium</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="high" value="high">
                                <label class="form-check-label" for="high">High</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between bg-light px-3 py-2 rounded-3"
                            style="background-color: #F4F4F4;">
                            <p class="mb-0 text-danger small">Low: &gt; 2 hours</p>
                            <p class="mb-0 text-danger small">Medium: &gt; 6 hours</p>
                            <p class="mb-0 text-danger small">High: &lt; 6 hours</p>
                        </div>

                    </form>
                </div>

                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-submit" data-bs-dismiss="modal">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detail-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-custom-width">
            <div class="modal-content p-4">
                <div class="d-flex justify-content-between">
                    <h5 class="modal-title fw-semibold">Task</h5>
                    <div class="d-flex justify-content-end">
                        <i class="bi bi-pencil-square" onclick="openEditModal()"></i>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <hr>
                <div class="task-title mb-4">
                    Wordpress plugin update
                </div>
                <div class="row g-0">
                    <div class="col-md-12 info-row">
                        <div class="info-icon"><i class="bi bi-three-dots"></i></div>
                        <div class="info-label">Status</div>
                        <div class="status-todo">To do</div>
                    </div>

                    <div class="col-md-12 info-row">
                        <div class="info-icon"><i class="bi bi-kanban"></i></div>
                        <div class="info-label">Project</div>
                        <div><strong>Website Management Company</strong></div>
                    </div>

                    <div class="col-md-12 info-row">
                        <div class="info-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="info-label">Timeline</div>
                        <div><strong>25 November 2024 - 30 November 2024</strong></div>
                    </div>

                    <div class="col-md-12 info-row">
                        <div class="info-icon"><i class="bi bi-people"></i></div>
                        <div class="info-label">Assignee</div>
                        <img src="https://i.pravatar.cc/32?img=12" class="avatar-sm" alt="Assignee">
                    </div>

                    <div class="col-md-12 info-row">
                        <div class="info-icon"><i class="bi bi-tag"></i></div>
                        <div class="info-label">Label</div>
                        <span class="task-label label-medium">Medium</span>
                        <span class="task-label label-engineer">Engineer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-task" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="d-flex justify-content-between">
                    <h5 class="modal-title">Edit Task</h5>
                    <button type="button" class="btn-close" onclick="closeEditModal()"></button>
                </div>
                <hr>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <input type="text" class="form-control" placeholder="Task name...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Level</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="level" value="Low">
                            <label class="form-check-label">Low</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="level" value="Medium">
                            <label class="form-check-label">Medium</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="level" value="High">
                            <label class="form-check-label">High</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src=" {{ asset('storage/js/main/tasks.js') }}"></script>
@endsection