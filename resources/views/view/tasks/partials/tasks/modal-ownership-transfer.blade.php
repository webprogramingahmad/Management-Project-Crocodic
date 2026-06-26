@php
    $ownershipRole = Auth::user()->role->role;
@endphp

<div class="modal fade" id="ownership-transfer-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="ownership-transfer-modal-title">Alihkan kepemilikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ownership-transfer-form" method="POST" action="#">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small mb-1">Task</label>
                        <div class="fw-semibold" id="ownership-transfer-task-name">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="ownership-transfer-to-user">Pindahkan ke</label>
                        <select class="form-select" id="ownership-transfer-to-user" name="to_user_id" required>
                            <option value="" disabled selected>Pilih SDM</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="ownership-transfer-reason">Alasan</label>
                        <textarea class="form-control" id="ownership-transfer-reason" name="reason" rows="3"
                            placeholder="Contoh: akan mengajukan izin tanggal ..." maxlength="2000"></textarea>
                        <div class="form-text" id="ownership-transfer-reason-hint">Wajib diisi untuk pengajuan staff.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark task-modal-submit" id="ownership-transfer-submit" data-task-form-submit>Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.__ownershipTransferRoutes = {
        staffRequest: @json($ownershipRole === 'staff' ? route('staff.task.ownership.request', ['id' => '__TASK__']) : null),
        directorReassign: @json($ownershipRole === 'director' ? route('director.task.ownership.reassign', ['id' => '__TASK__']) : null),
    };
</script>
