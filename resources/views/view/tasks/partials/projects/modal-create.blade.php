<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header border-b-8 border-black pb-2">
            <h5 class="modal-title fw-bold" id="transferTaskModalLabel">Create Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" @if ($role === 'director')
        action="{{ route('director.project.task.store', $project->id) }}" @elseif($role === 'staff')
            action="{{ route('staff.project.task.store', $project->id) }}" @else
            action="{{ route('executive.project.task.store', $project->id)}}" @endif>
            @csrf
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label">Task</label>
                    <input type="text" class="form-control" name="name" placeholder="Task name...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Project</label>
                    <select class="form-select" name="id_project" required disabled>
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    </select>
                </div>
                <div class="mb-2  d-flex align-items-center gap-3">
                    <label class="form-label d-block">Task Level</label>
                    @foreach ($difficulties as $diff)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="id_difficulty" id="{{ $diff->difficulty }}"
                                value="{{ $diff->id }}" required>
                            <label class="form-check-label" for="{{ $diff->difficulty }}">{{ $diff->difficulty }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between bg-light px-3 py-2 rounded-3"
                    style="background-color: #F4F4F4;">
                    <p class="mb-0 text-danger small">Low: &gt; 2 hours</p>
                    <p class="mb-0 text-danger small">Medium: &gt; 6 hours</p>
                    <p class="mb-0 text-danger small">High: &lt; 6 hours</p>
                </div>

                <div class="mb-0 mt-3">
                    <label class="form-label" for="create-project-task-description">Notes</label>
                    <textarea class="form-control" id="create-project-task-description" name="description" rows="3"
                        placeholder="Notes...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if ($errors->any())
                @dd($errors)
            @endif

            <div class="modal-footer border-0 pt-1">
                <button type="submit" class="btn btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>