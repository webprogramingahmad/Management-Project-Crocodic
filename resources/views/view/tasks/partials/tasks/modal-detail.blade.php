@php
    $detailRole = Auth::user()->role->role;
    $ownershipApproveUrlTpl = $detailRole === 'director'
        ? route('director.task.ownership.approve', ['id' => '__TASK__', 'requestId' => '__REQUEST__'])
        : null;
    $ownershipRejectUrlTpl = $detailRole === 'director'
        ? route('director.task.ownership.reject', ['id' => '__TASK__', 'requestId' => '__REQUEST__'])
        : null;
    $submissionsUrlTpl = match ($detailRole) {
        'staff' => route('staff.task.submissions', ['id' => '__TASK_ID__']),
        'director' => route('director.task.submissions', ['id' => '__TASK_ID__']),
        'executive' => route('executive.task.submissions', ['id' => '__TASK_ID__']),
        default => null,
    };
@endphp

<div class="modal-dialog modal-dialog-centered modal-custom-width">
    <div class="modal-content">

        <div class="d-flex justify-content-between align-items-center modal-header">
            <h5 class="modal-title fw-semibold">Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-4">
            <div class="task-title mb-4" style="font-size: 1.7rem; font-weight: bold;"></div>
            <div class="row g-0">

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

    <div class="col-12 info-row d-flex align-items-center mb-2">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-kanban"></i>
            <span>Project</span>
        </div>
        <div class="col-7 fw-bold modal-project"></div>
    </div>

    <div class="col-12 info-row d-flex align-items-center mb-2">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-calendar-event"></i>
            <span>Timeline</span>
        </div>
        <div class="col-7 fw-bold modal-timeline"></div>
    </div>

    <div class="col-12 info-row d-flex align-items-start mb-2 modal-time-tracking-row">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-stopwatch"></i>
            <span>Time Tracking</span>
        </div>
        <div class="col-7">
            <div class="small mb-1">
                <span class="text-muted">In Progress:</span>
                <span class="fw-semibold modal-progress-time">-</span>
            </div>
            <div class="modal-revision-times"></div>
        </div>
    </div>

    <div class="col-12 info-row d-flex align-items-start mb-0 modal-task-description-row d-none">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-card-text"></i>
            <span>Description</span>
        </div>
        <div class="col-7 modal-task-description small text-break"></div>
    </div>

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

    <div class="col-12 mt-3" id="detail-task-actions-row">
        <div class="border rounded-3 p-3" style="border-color: rgba(224,224,224,0.7) !important;">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-view-work-results">
                    <i class="bi bi-folder2-open me-1"></i> Hasil kerja
                </button>

                @if ($detailRole === 'director')
                    <button type="button" class="btn btn-sm btn-outline-primary d-none" id="detail-review-decision-btn">
                        <i class="bi bi-clipboard-check me-1"></i> Review decision
                    </button>
                @endif

                <div id="detail-ownership-section" class="d-none d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary d-none" id="btn-request-ownership-transfer">
                        Ajukan alih kepemilikan
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary d-none" id="btn-direct-ownership-reassign">
                        Alihkan kepemilikan
                    </button>
                </div>
            </div>

            <div id="detail-ownership-pending" class="d-none mt-3">
                <p class="small mb-2 text-warning-emphasis" id="detail-ownership-pending-text"></p>
                <div class="small text-muted mb-2">
                    <span class="d-block"><strong>Penerima diajukan:</strong> <span id="detail-ownership-pending-to">-</span></span>
                    <span class="d-block"><strong>Alasan:</strong> <span id="detail-ownership-pending-reason">-</span></span>
                </div>
                <div id="detail-ownership-review-actions" class="d-none">
                    <form id="form-ownership-approve" method="POST" action="#">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="ownership-approve-to-user">Penerima (boleh diubah)</label>
                            <select class="form-select form-select-sm" id="ownership-approve-to-user" name="to_user_id"></select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1" for="ownership-approve-note">Catatan (opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="ownership-approve-note" name="review_note" maxlength="1000">
                        </div>
                    </form>
                    <form id="form-ownership-reject" method="POST" action="#">
                        @csrf
                        <input type="hidden" name="review_note" id="ownership-reject-note" value="">
                    </form>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" form="form-ownership-approve" class="btn btn-sm btn-success">Setuju</button>
                        <button type="submit" form="form-ownership-reject" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Tolak pengajuan alih kepemilikan?')">Tolak</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

        </div>

    </div>
</div>

<script>
    window.__taskSubmissionsUrlTemplate = @json($submissionsUrlTpl);
    window.__ownershipDirectorRoutes = {
        approve: @json($ownershipApproveUrlTpl),
        reject: @json($ownershipRejectUrlTpl),
    };
</script>
