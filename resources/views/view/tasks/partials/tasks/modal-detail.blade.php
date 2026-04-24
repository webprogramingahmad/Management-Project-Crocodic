<div class="modal-dialog modal-dialog-centered modal-custom-width">
    <div class="modal-content">

        <div class="d-flex justify-content-between align-items-center modal-header">
            <h5 class="modal-title fw-semibold">Task</h5>
            <div class="d-flex justify-content-end align-items-center">
                <i class="bi bi-pencil-square" id="edit-task-link" role="button" tabindex="0" aria-label="Edit task"
                    style="cursor:pointer;">
                </i>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
            </div>
        </div>

        <div class="modal-body p-4">
            <div class="task-title mb-4" style="font-size: 1.7rem; font-weight: bold;"></div>
            <div class="row g-0">

    <!-- Assignee -->
    <div class="col-12 info-row d-flex align-items-center mb-2">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-people"></i>
            <span>Assignee</span>
        </div>
        <div class="col-7 modal-user d-flex align-items-center gap-2">
            <img class="avatar-sm rounded-circle d-none"
                 style="width:32px;height:32px;object-fit:cover;">
            <div class="modal-user-initial rounded-circle d-flex align-items-center justify-content-center fw-semibold d-none"
                 style="width:32px;height:32px;font-size:12px;background:#0D8ABC;color:white;">
            </div>
            <span class="modal-username fw-semibold d-none">-</span>
        </div>
    </div>

    <!-- Project -->
    <div class="col-12 info-row d-flex align-items-center mb-2">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-kanban"></i>
            <span>Project</span>
        </div>
        <div class="col-7 fw-bold modal-project"></div>
    </div>

    <!-- Timeline -->
    <div class="col-12 info-row d-flex align-items-center mb-2">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-calendar-event"></i>
            <span>Timeline</span>
        </div>
        <div class="col-7 fw-bold modal-timeline"></div>
    </div>

    <!-- Keterangan / tautan (opsional) -->
    <div class="col-12 info-row d-flex align-items-start mb-0 modal-task-description-row d-none">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-link-45deg"></i>
            <span>Notes</span>
        </div>
        <div class="col-7 modal-task-description small text-break"></div>
    </div>

    <!-- Level / Status -->
    <div class="col-12 info-row d-flex align-items-center">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-tag"></i>
            <span>Level / Status</span>
        </div>
        <div class="col-7 d-flex gap-2">
            <span class="modal-difficulty btn btn-sm rounded-2 border-0 task-meta-pill"></span>
            <span class="status-todo btn btn-sm rounded-2 border-0 task-meta-pill"></span>
        </div>
    </div>

</div>

        </div>

        @if (Auth::user()->role->role === 'director')
            <div class="modal-footer border-top-0 pt-0 d-none" id="detail-task-review-footer">
                <button type="button" class="btn btn-outline-primary ms-auto" id="detail-review-decision-btn">
                    Review decision
                </button>
            </div>
        @endif

    </div>
</div>