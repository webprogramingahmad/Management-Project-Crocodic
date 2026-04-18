@extends('layouts.app')

@section('title')
    Edit Project {{ $project->name }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/project-create.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@php
    $role = Auth::user()->role->role;
    $durationInitial = \App\Support\ProjectDuration::label(
        old('start_date', $project->start_date?->format('Y-m-d')),
        old('end_date', $project->end_date?->format('Y-m-d'))
    );
@endphp

@section('content')
    <div class="container-fluid gx-0 px-1 py-1">
        <form method="POST" @if ($role === 'executive') action="{{ route('executive.project.update', $project->id) }}" @else
        action="{{ route('director.project.update', $project->id) }}" @endif>
            @csrf
            @method('PUT')
            <div class="row ps-3 g-3">
                <div class="card gx-0 col-12 col-lg-5" style="max-width: 40%; border-radius: 15px; border-color: #E0E0E0CE;">
                <div class="card-body">
                    <div class="form-container gx-0 h-100 mx-1">
                        <h5 class="fw-bold mb-4">Edit Project {{ ucwords($project->name) }}</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Project</label>
                            <input type="text" name="name" class="form-control" placeholder="Project name..."
                                value="{{ old('name', ucwords($project->name)) }}" required>
                        </div>

                        <div class="row mx-0 gx-2 mb-3 align-items-end">
                            <div class="col-12 col-md-4 ps-md-0">
                                <label class="form-label text-muted small mb-1">Start</label>
                                <input type="date" class="form-control form-control-sm project-date-picker" name="start_date"
                                    value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted small mb-1">End</label>
                                <input type="date" class="form-control form-control-sm project-date-picker" name="end_date"
                                    value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}" required>
                            </div>
                            @include('view.projects.partials.project-duration-field', ['initialLabel' => $durationInitial])
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Level Project</label>
                            <select name="id_difficulty" class="form-select" required>
                                <option value="" disabled @selected(!old('id_difficulty', $project->id_difficulty))>-- Select Level --</option>
                                @foreach($difficulties as $diff)
                                    <option value="{{ $diff->id }}" @selected(old('id_difficulty', $project->id_difficulty) == $diff->id)>{{ $diff->difficulty }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Status Project</label>
                            <select name="id_status" class="form-select" required>
                                <option disabled>-- Select Status --</option>
                                @foreach($statusprojects as $status)
                                    <option value="{{ $status->id }}" {{ old('id_status', $project->id_status) == $status->id ? 'selected' : '' }}>{{ $status->status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-muted">About Project</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="About project..."
                                required> {{ old('description', $project->description) }}</textarea>
                        </div>
                    </div>
                </div>
                </div>

                <div class="col-12 col-lg-7 ps-2">
                    <div class="card" style="height:100% ;border-radius: 15px; border-color: #E0E0E0CE;">
                    <div class="card-body">
                        <div class="form-container">
                            <h5 class="fw-bold mb-4">SDM</h5>
                            <div class="mb-3 mx-1">
                                <label class="form-label text-muted">Project Director</label>
                                @if ($role === 'director')
                                    <input type="hidden" name="id_director" value="{{ Auth::id() }}">
                                    <select class="form-select" disabled>
                                        @foreach ($directors as $director)
                                            <option value="{{ $director->id }}" @selected($director->id == $project->id_director)>
                                                {{ $director->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="id_director" class="form-select" required>
                                        @foreach ($directors as $director)
                                            <option value="{{ $director->id }}" @selected($director->id == old('id_director', $project->id_director))>
                                                {{ $director->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <div class="mb-3 mx-1">
                                <label class="form-label">Add SDM</label>
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
                            <div id="sdm-container" class="row gx-2 gy-2">
                                {{-- SDM container will be rendered by JS --}}
                            </div>
                        </div>
                </div>
                <div class="d-flex justify-content-end gap-3 m-4">
                    <a @if ($role === 'executive') href="{{ route('executive.projects.index') }}" @else
                    href="{{ route('director.projects.index') }}" @endif
                        class="btn btn-cancel px-5 py-0 rounded-3" style="height: 35px; line-height:35px">Cancel</a>
                    <button type="submit" class="btn btn-create px-5 py-0 rounded-3" style="background-color:black; color:#ffffff; height:35px; line-height:35px">Edit</button>
                </div>
            </div>
        </div>
        </div>
        </form>
    </div>
@endsection

@section('js')
    @php
        $sdmByDivision = $project->sdms->groupBy('id_divisi')->map(function ($g) {
            return $g->pluck('id')->values()->take(2)->all();
        });
    @endphp
    <script>
        const divisions = @json($divisions);
        const users = @json($divisions->flatMap->users);
        const selectedMap = @json($sdmByDivision);
    </script>

    <script>
        (function () {
            const divisionSelect = document.getElementById('division-select');
            const sdmContainer = document.getElementById('sdm-container');
            const limitMessageEl = document.getElementById('sdm-limit-message');

            if (!divisionSelect || !sdmContainer) return;

            const addedDivisions = new Set();
            const MAX_DIVISIONS = divisions.length;

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
                opt0.textContent = '— Select SDM —';
                select.appendChild(opt0);

                divisionUsers.forEach(function (u) {
                    const o = document.createElement('option');
                    o.value = u.id;
                    o.textContent = u.name;
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

                const divisionUsers = users.filter(function (u) {
                    return String(u.id_divisi) === String(division.id);
                });

                const selectsWrap = document.createElement('div');
                selectsWrap.className = 'd-flex flex-column gap-2';

                if (divisionUsers.length === 0) {
                    const sel = document.createElement('select');
                    sel.className = 'form-select';
                    sel.disabled = true;
                    const opt = document.createElement('option');
                    opt.textContent = '-- No SDM available --';
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
                    addSdmBtn.innerHTML = '<i class="bi bi-plus-lg me-1" style="font-size:0.75rem;line-height:1;vertical-align:-0.05em;"></i><span style="font-size:0.7rem">Add SDM</span>';
                    actions.insertBefore(addSdmBtn, removeBtn);

                    function showSecondSlot(preVal) {
                        if (slot2Host.querySelector('select')) return;
                        const s2 = buildSdmsSelect(division.id, divisionUsers, preVal || '');
                        const removeSecond = document.createElement('button');
                        removeSecond.type = 'button';
                        removeSecond.className = 'btn btn-link btn-sm text-secondary p-0 align-self-start';
                        removeSecond.textContent = 'Remove second SDM';
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

            (function prefill() {
                try {
                    const map = selectedMap || {};
                    Object.keys(map).forEach(function (divId) {
                        if (addedDivisions.size >= MAX_DIVISIONS) return;
                        const division = divisions.find(function (d) {
                            return String(d.id) === String(divId);
                        });
                        if (!division) return;
                        const raw = map[divId];
                        const pre = Array.isArray(raw) ? raw : (raw ? [raw] : []);
                        const col = createDivisionDropdown(division, pre);
                        sdmContainer.appendChild(col);
                        addedDivisions.add(String(divId));
                        const opt = divisionSelect.querySelector('option[value="' + divId + '"]');
                        if (opt) opt.hidden = true;
                    });
                } catch (e) {}
            })();

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
                const opt = divisionSelect.querySelector('option[value="' + divisionId + '"]');
                if (opt) opt.hidden = true;
                hideLimitMessage();

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
    <script src=" {{ asset('build/js/main/project-edit.js') }}"></script>
@endsection