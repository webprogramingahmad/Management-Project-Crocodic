@extends('layouts.app')

@section('title')
    Create Project
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/project-create.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Cancel: teks & border selalu terbaca (btn-cancel / btn-outline-dark + project-create.css bisa bikin warna default = background) */
        .project-form-cancel-btn {
            color: #212529 !important;
            background-color: #ffffff !important;
            border: 1px solid #6c757d !important;
            text-decoration: none !important;
        }
        .project-form-cancel-btn:hover {
            color: #000 !important;
            background-color: #f8f9fa !important;
            border-color: #495057 !important;
        }
        html[data-theme="dark"] .project-form-cancel-btn {
            color: #fafafa !important;
            background-color: rgba(255, 255, 255, 0.12) !important;
            border-color: #a1a1aa !important;
        }
        html[data-theme="dark"] .project-form-cancel-btn:hover {
            background-color: rgba(255, 255, 255, 0.18) !important;
            border-color: #d4d4d8 !important;
        }
    </style>
@endsection

@php
    $role = Auth::user()->role->role;
    $durationInitial = \App\Support\ProjectDuration::label(old('start_date'), old('end_date'));
@endphp

@section('content')
    <div class="container-fluid gx-0">
        <form method="POST" @if ($role === 'executive') action="{{ route('executive.project.store') }}" @elseif ($role === 'director') action="{{ route('director.project.store') }}" @endif>
            @csrf
            <div class="row ps-3 g-3">
                <div class="card gx-0 col-12 col-lg-5" style="border-radius: 15px; border: 1px solid #E0E0E0CE;">
                <div class="card-body">
                    <div class="form-container gx-0 h-100 mx-1">
                        <h5 class="fw-bold mb-4">New Project</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Project</label>
                            <input type="text" name="name" class="form-control" placeholder="Project name...">
                        </div>

                        <div class="row mx-0 gx-2 mb-3 align-items-end">
                            <div class="col-12 col-md-4 ps-md-0">
                                <label class="form-label text-muted small mb-1">Start</label>
                                <input type="text" class="form-control form-control-sm project-date-picker" placeholder="Start" name="start_date"
                                    value="{{ old('start_date') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted small mb-1">End</label>
                                <input type="text" class="form-control form-control-sm project-date-picker" placeholder="End" name="end_date"
                                    value="{{ old('end_date') }}">
                            </div>
                            @include('view.projects.partials.project-duration-field', ['initialLabel' => $durationInitial])
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Level Project</label>
                            <select name="id_difficulty" class="form-select" required>
                                <option disabled selected>-- Select Level --</option>
                                @foreach($difficulties as $difficulty)
                                    <option value="{{ $difficulty->id }}">{{ $difficulty->difficulty }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Status Project</label>
                            <select name="id_status" class="form-select" required>
                                <option disabled selected>-- Select Status --</option>
                                @foreach($statusprojects as $status)
                                    <option value="{{ $status->id }}">{{ $status->status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label text-muted">About Project</label>
                            <textarea class="form-control" name="description" rows="4"
                                placeholder="About project..."></textarea>
                        </div>
                    </div>
                </div>
                </div>
            
                <div class="col-12 col-lg-7 ps-2">
                <div class="card" style="height:100%; border-radius: 15px; border: 1px solid #E0E0E0CE;">
                    <div class="card-body">
                    <div class="form-container">
                        <h5 class="fw-bold mb-4">Staff</h5>
                        <div class="mb-3 mx-1">
                            <label class="form-label text-muted">Project Director</label>
                            <select name="id_director" class="form-select" @if ($role === 'director') disabled @endif>
                                @if ($role === 'director')
                                    @php $wDir = $workloadByUserId[Auth::id()] ?? ['count' => 0, 'max_days' => 0]; @endphp
                                    <option value="{{ Auth::user()->id }}" selected>{{ ucwords(Auth::user()->name) }} ({{ $wDir['count'] }} project - {{ $wDir['max_days'] }} hari)</option>
                                @else
                                    <option value="" selected>-- Select director --</option>
                                    @foreach ($directors as $director)
                                        @php $wDir = $workloadByUserId[$director->id] ?? ['count' => 0, 'max_days' => 0]; @endphp
                                        <option value="{{ $director->id }}">{{ ucwords($director->name) }} ({{ $wDir['count'] }} project - {{ $wDir['max_days'] }} hari)</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mb-3 mx-1">
                            <label class="form-label">Add Staff</label>
                            <select id="division-select" class="form-select">
                                <option value="">-- Select division --</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->divisi }}</option>
                                @endforeach
                            </select>
                            <div id="sdm-limit-message" class="text-danger small mt-1" style="display:none"></div>
                            @error('sdms')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Container SDM --}}
                        <div id="sdm-container" class="row gx-2 gy-2"></div>
                    </div>
                </div>
                    <div class="d-flex justify-content-end gap-3 m-4">
                        @if ($role === 'executive')
                            <a href="{{ route('executive.projects.index') }}" class="btn project-form-cancel-btn px-5 py-0 rounded-3" style="height: 35px; line-height: 35px;">Cancel</a>
                        @elseif($role === 'director')
                            <a href="{{ route('director.projects.index') }}" class="btn project-form-cancel-btn px-5 py-0 rounded-3" style="height: 35px; line-height: 35px;">Cancel</a>
                        @endif
                        <button type="submit" class="btn btn-create px-5 py-0 rounded-3" style="background-color: black; color: #ffffff; height: 35px; line-height: 35px;">Create</button>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
@endsection

@section('js')
    <script>
        window.projectFormData = {
            divisions: @json($divisions),
            users: @json($usersForProjectJs)
        };

        (function () {
            const divisions = window.projectFormData.divisions || [];
            const users = window.projectFormData.users || [];

            const divisionSelect = document.getElementById('division-select');
            const sdmContainer = document.getElementById('sdm-container');
            const directorSelectEl = document.querySelector('select[name="id_director"]');

            if (!divisionSelect || !sdmContainer) return;

            function formatUserLabel(u) {
                var c = u.workload_count != null ? u.workload_count : 0;
                var d = u.workload_max_days != null ? u.workload_max_days : 0;
                return u.name + ' (' + c + ' project - ' + d + ' hari)';
            }

            function getSelectedDirectorId() {
                if (!directorSelectEl) return '';
                if (directorSelectEl.disabled) {
                    var opt = directorSelectEl.querySelector('option[selected]') || directorSelectEl.querySelector('option');
                    return opt ? String(opt.value) : '';
                }
                return directorSelectEl.value ? String(directorSelectEl.value) : '';
            }

            function getDivisionUsersFiltered(divisionId) {
                var directorId = getSelectedDirectorId();
                return users.filter(function (u) {
                    if (String(u.id_divisi) !== String(divisionId)) return false;
                    if (directorId && String(u.id) === directorId) return false;
                    return true;
                });
            }

            function refreshAllSdmSelectsFromDirector() {
                sdmContainer.querySelectorAll('[data-division]').forEach(function (col) {
                    var divisionId = col.dataset.division;
                    var divisionUsers = getDivisionUsersFiltered(divisionId);
                    col.querySelectorAll('select[name^="sdms["]').forEach(function (sel) {
                        if (sel.disabled) return;
                        var currentVal = sel.value;
                        while (sel.firstChild) sel.removeChild(sel.firstChild);
                        var opt0 = document.createElement('option');
                        opt0.value = '';
                        opt0.textContent = '— Select staff —';
                        sel.appendChild(opt0);
                        divisionUsers.forEach(function (u) {
                            var o = document.createElement('option');
                            o.value = u.id;
                            o.textContent = formatUserLabel(u);
                            sel.appendChild(o);
                        });
                        if (currentVal && divisionUsers.some(function (u) { return String(u.id) === String(currentVal); })) {
                            sel.value = currentVal;
                        } else {
                            sel.value = '';
                        }
                    });
                    var selectsWrap = col.querySelector('.d-flex.flex-column.gap-2');
                    if (selectsWrap) {
                        selectsWrap.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }

            if (directorSelectEl && !directorSelectEl.disabled) {
                directorSelectEl.addEventListener('change', refreshAllSdmSelectsFromDirector);
            }

            const addedDivisions = new Set();
            const MAX_DIVISIONS = divisions.length;
            const limitMessageEl = document.getElementById('sdm-limit-message');

            function showLimitMessage(msg) {
                if (limitMessageEl) {
                    limitMessageEl.textContent = msg;
                    limitMessageEl.style.display = 'block';
                } else {
                    alert(msg);
                }
            }

            function hideLimitMessage() {
                if (limitMessageEl) {
                    limitMessageEl.textContent = '';
                    limitMessageEl.style.display = 'none';
                }
            }

            function buildSdmsSelect(divisionId, divisionUsers, preValue) {
                const select = document.createElement('select');
                select.className = 'form-select';
                select.name = 'sdms[' + divisionId + '][]';

                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = '— Select staff —';
                select.appendChild(opt0);

                divisionUsers.forEach(function (u) {
                    const o = document.createElement('option');
                    o.value = u.id;
                    o.textContent = formatUserLabel(u);
                    select.appendChild(o);
                });

                if (preValue) {
                    select.value = preValue;
                }
                return select;
            }

            function createDivisionDropdown(division, preselected) {
                if (preselected === void 0) preselected = [];
                const col = document.createElement('div');
                col.className = 'col-12 col-md-6 col-lg-4';
                col.dataset.division = division.id;

                const container = document.createElement('div');
                container.className = 'd-flex flex-column gap-2 p-2 border rounded-3 project-staff-select-card';

                const headRow = document.createElement('div');
                headRow.className = 'd-flex align-items-center justify-content-between gap-1 flex-nowrap';

                const label = document.createElement('div');
                label.className = 'fw-semibold flex-grow-1 min-w-0 text-truncate me-1';
                label.textContent = division.divisi;
                label.title = division.divisi;

                const actions = document.createElement('div');
                actions.className = 'd-flex align-items-center gap-1 flex-shrink-0';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-outline-danger remove-division py-0 px-1 lh-1 border';
                removeBtn.setAttribute('aria-label', 'Remove division from list');
                removeBtn.innerHTML = '<i class="bi bi-trash" style="font-size:0.75rem;line-height:1;vertical-align:-0.05em;"></i>';

                actions.appendChild(removeBtn);
                headRow.appendChild(label);
                headRow.appendChild(actions);

                const divisionUsers = getDivisionUsersFiltered(division.id);

                const selectsWrap = document.createElement('div');
                selectsWrap.className = 'd-flex flex-column gap-2';

                if (divisionUsers.length === 0) {
                    const sel = document.createElement('select');
                    sel.className = 'form-select';
                    sel.disabled = true;
                    const opt = document.createElement('option');
                    opt.textContent = '-- No staff available --';
                    sel.appendChild(opt);
                    selectsWrap.appendChild(sel);
                } else {
                    function syncSecondStaffOptions() {
                        const selects = selectsWrap.querySelectorAll('select[name^="sdms["]');
                        if (selects.length < 2) return;
                        const firstSel = selects[0];
                        const secondSel = selects[1];
                        const pick = firstSel.value;
                        const opts = secondSel.querySelectorAll('option');
                        opts.forEach(function (opt, idx) {
                            if (idx === 0 || opt.value === '') return;
                            const hide = Boolean(pick) && String(opt.value) === String(pick);
                            opt.hidden = hide;
                            opt.disabled = hide;
                        });
                        if (pick && String(secondSel.value) === String(pick)) {
                            secondSel.value = '';
                        }
                    }

                    selectsWrap.addEventListener('change', syncSecondStaffOptions);

                    const pre = Array.isArray(preselected) ? preselected : (preselected ? [preselected] : []);
                    const s1 = buildSdmsSelect(division.id, divisionUsers, pre[0] || '');
                    selectsWrap.appendChild(s1);

                    const slot2Host = document.createElement('div');
                    slot2Host.className = 'd-flex flex-column gap-1';
                    slot2Host.style.display = 'none';

                    const addSdmBtn = document.createElement('button');
                    addSdmBtn.type = 'button';
                    addSdmBtn.className = 'btn btn-outline-secondary py-0 px-1 text-nowrap';
                    addSdmBtn.innerHTML = '<i class="bi bi-plus-lg me-1" style="font-size:0.75rem;line-height:1;vertical-align:-0.05em;"></i><span style="font-size:0.7rem">Add Staff</span>';
                    actions.insertBefore(addSdmBtn, removeBtn);

                    function showSecondSlot(preVal) {
                        if (slot2Host.querySelector('select')) return;
                        const s2 = buildSdmsSelect(division.id, divisionUsers, preVal || '');
                        const removeSecond = document.createElement('button');
                        removeSecond.type = 'button';
                        removeSecond.className = 'btn btn-link btn-sm text-secondary p-0 align-self-start';
                        removeSecond.textContent = 'Remove second staff';
                        removeSecond.addEventListener('click', function () {
                            slot2Host.innerHTML = '';
                            slot2Host.style.display = 'none';
                            addSdmBtn.style.display = '';
                        });
                        slot2Host.appendChild(s2);
                        slot2Host.appendChild(removeSecond);
                        slot2Host.style.display = '';
                        addSdmBtn.style.display = 'none';
                        syncSecondStaffOptions();
                    }

                    addSdmBtn.addEventListener('click', function () {
                        showSecondSlot();
                    });

                    selectsWrap.appendChild(slot2Host);

                    if (pre[1]) {
                        showSecondSlot(pre[1]);
                    }
                    syncSecondStaffOptions();
                }

                container.appendChild(headRow);
                container.appendChild(selectsWrap);
                col.appendChild(container);

                removeBtn.addEventListener('click', function () {
                    sdmContainer.removeChild(col);
                    addedDivisions.delete(String(division.id));
                    const opt = divisionSelect.querySelector('option[value="' + division.id + '"]');
                    if (opt) opt.hidden = false;
                    hideLimitMessage();
                });

                return col;
            }

            divisionSelect.addEventListener('change', function (e) {
                const divisionId = e.target.value;
                if (!divisionId) return;
                if (addedDivisions.has(String(divisionId))) {
                    divisionSelect.value = '';
                    return;
                }

                if (addedDivisions.size >= MAX_DIVISIONS) {
                    showLimitMessage('Semua divisi sudah ditambahkan');
                    divisionSelect.value = '';
                    return;
                }

                const division = divisions.find(function (d) {
                    return String(d.id) === String(divisionId);
                });
                if (!division) {
                    divisionSelect.value = '';
                    return;
                }

                const row = createDivisionDropdown(division, []);
                sdmContainer.appendChild(row);
                addedDivisions.add(String(divisionId));
                hideLimitMessage();

                const opt = divisionSelect.querySelector('option[value="' + divisionId + '"]');
                if (opt) opt.hidden = true;

                divisionSelect.value = '';
            });

            const projectForm = document.querySelector('form');
            if (projectForm) {
                projectForm.addEventListener('submit', function () {
                    sdmContainer.querySelectorAll('[data-division]').forEach(function (col) {
                        const selects = col.querySelectorAll('select[name^="sdms["]');
                        if (!selects.length) return;
                        const any = Array.prototype.some.call(selects, function (sel) {
                            return sel.value;
                        });
                        if (!any) col.remove();
                    });
                });
            }
        })();
    </script>
    @include('view.projects.partials.project-duration-script')
    @include('view.projects.partials.project-date-picker-script')
    <script src="{{ asset('build/js/main/project-create.js') }}"></script>
@endsection
