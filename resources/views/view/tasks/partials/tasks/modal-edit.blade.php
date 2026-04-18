@php
    $role = Auth::user()->role->role;
@endphp

<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title fw-bold" id="editTaskLabel">Edit Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="form-edit-task" method="POST"
            @if ($role === 'staff')
                action="{{ route('staff.task.update') }}"
            @elseif($role === 'executive')
                action="{{ route('executive.task.update') }}"
            @else
                action="{{ route('director.task.update') }}"
            @endif>
            @csrf
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_task_id">

                <div class="mb-3">
                    <label for="edit_task_name" class="form-label">Nama Task</label>
                    <input type="text" name="name" id="edit_task_name" class="form-control" required>
                </div>

                <div class="mb-2 d-flex align-items-center gap-3">
                    <label class="form-label d-block">Task Level</label>
                    @foreach ($difficulties as $diff)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="id_difficulty" id="edit_diff_{{ $diff->id }}"
                                value="{{ $diff->id }}">
                            <label class="form-check-label" for="edit_diff_{{ $diff->id }}">
                                {{ $diff->difficulty }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between bg-light px-3 py-2 rounded-3">
                    <p class="mb-0 text-danger small">Low: &gt; 2 hours</p>
                    <p class="mb-0 text-danger small">Medium: &gt; 6 hours</p>
                    <p class="mb-0 text-danger small">High: &lt; 6 hours</p>
                </div>

                <div class="mb-0 mt-3">
                    <label class="form-label" for="edit_task_description">Notes</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="edit_task_description"
                        name="description" rows="3" placeholder="Notes...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
    </div>
</div>
