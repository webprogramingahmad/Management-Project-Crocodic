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
            <div class="task-title mb-4"></div>
            <div class="row g-0">
                <div class="col-md-12 info-row">
                    <div class="info-icon"><i class="bi bi-people"></i></div>
                    <div class="info-label">Assignee</div>
                    <div class="modal-user d-flex align-items-center gap-2">
                        <img class="avatar-sm rounded-circle d-none"
                            style="width: 32px; height: 32px; object-fit: cover;">
                        <div class="modal-user-initial rounded-circle d-flex align-items-center justify-content-center fw-semibold d-none"
                            style="width:32px; height:32px; font-size:12px; background:#0D8ABC; color:white;">
                        </div>
                        <span class="modal-username fw-semibold d-none">-</span>
                    </div>
                </div>
                <div class="col-md-12 info-row">
                    <div class="info-icon"><i class="bi bi-kanban"></i></div>
                    <div class="info-label">Project</div>
                    <div class="modal-project fw-bold"></div>
                </div>
                <div class="col-md-12 info-row">
                    <div class="info-icon"><i class="bi bi-calendar-event"></i></div>
                    <div class="info-label">Timeline</div>
                    <div class="modal-timeline fw-bold"></div>
                </div>
                <div class="col-md-12 info-row align-items-start modal-task-description-row d-none">
                    <div class="info-icon"><i class="bi bi-link-45deg"></i></div>
                    <div class="info-label">Notes</div>
                    <div class="modal-task-description small text-break"></div>
                </div>
                <div class="col-md-12 info-row">
                    <div class="info-icon"><i class="bi bi-tag"></i></div>
                    <div class="info-label">Level / Status</div>
                    <div class="d-flex gap-2">
                        <span class="modal-difficulty btn btn-sm rounded-2 border-0 task-meta-pill">-</span>
                        <span class="status-todo btn btn-sm rounded-2 border-0 task-meta-pill">-</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>