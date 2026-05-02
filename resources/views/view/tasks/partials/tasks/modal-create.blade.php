<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header border-b-8 border-black pb-2">
            <h5 class="modal-title fw-bold" id="transferTaskModalLabel">Create Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" @if ($role === 'director') action="{{ route('director.task.store') }}"
        @elseif($role === 'staff') action="{{ route('staff.task.store') }}" @else
            action="{{ route('executive.task.store')}}" @endif>
            @csrf
            <div class="modal-body p-3">
                <div class="mb-3" id="task-name-group">
                    <label class="form-label">Task</label>
                    <input type="text" class="form-control" name="name" placeholder="Task name">
                </div>
                <div class="mb-3" id="project-group">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="id_project" required>
                        <option value="" selected disabled>Select project</option>
                        @foreach (($projectsForTaskForms ?? $projects) as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2  d-flex align-items-center gap-2">
                    <label class="form-label d-block">Task Level</label>
                    @foreach ($difficulties as $diff)
                        @php $diffName = strtolower($diff->difficulty); @endphp
                        @if (!in_array($diffName, ['stand by', 'standby']))
                        <div class="form-check form-check-inline">
                            <input class="form-check-input task-level" type="radio" name="id_difficulty" id="{{ $diff->difficulty }}"
                                value="{{ $diff->id }}" required>
                            <label class="form-check-label" for="{{ $diff->difficulty }}">{{ $diff->difficulty }}</label>
                        </div>
                        @endif
                    @endforeach
                    @if ($role === 'staff' || $role === 'director')
                        {{-- Stand By manual --}}
                        <div class="form-check form-check-inline ms-2">
                            <input class="form-check-input task-level"
                                type="radio"
                                name="id_difficulty"
                                id="standby-level"
                                value="standby"
                                data-type="standby">
                            <label class="form-check-label" for="standby-level">Stand By</label>
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between bg-light px-3 py-2 rounded-3"
                    style="background-color: #F4F4F4;">
                    <p class="mb-0 text-danger small">Low: &lt; 2 hours</p>
                    <p class="mb-0 text-danger small">Medium: &lt; 6 hours</p>
                    <p class="mb-0 text-danger small">High: &gt; 6 hours</p>
                </div>

                <div class="mb-0 mt-3">
                    <label class="form-label" for="create-task-description">Notes</label>
                    <textarea class="form-control" id="create-task-description" name="description" rows="3"
                        placeholder="Notes...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-submit task-modal-submit">Create Task</button>
            </div>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const standbyRadio = document.getElementById('standby-level');
            const taskNameGroup = document.getElementById('task-name-group');
            const projectGroup = document.getElementById('project-group');
            const allLevelRadios = document.querySelectorAll('input[name="id_difficulty"]');
            function toggleStandbyFields() {
                if (standbyRadio && standbyRadio.checked) {
                    taskNameGroup.style.display = 'none';
                    projectGroup.style.display = 'none';
                    // Remove required attribute
                    taskNameGroup.querySelector('input').removeAttribute('required');
                    projectGroup.querySelector('select').removeAttribute('required');
                } else {
                    taskNameGroup.style.display = '';
                    projectGroup.style.display = '';
                    // Add required attribute
                    taskNameGroup.querySelector('input').setAttribute('required', 'required');
                    projectGroup.querySelector('select').setAttribute('required', 'required');
                }
            }
            allLevelRadios.forEach(function(radio) {
                radio.addEventListener('change', toggleStandbyFields);
            });
            // Initial state
            toggleStandbyFields();
        });
        </script>
    </div>
</div>