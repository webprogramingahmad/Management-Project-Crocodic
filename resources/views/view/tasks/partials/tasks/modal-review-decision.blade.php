@if (Auth::user()->role->role === 'director')
    <div class="modal fade" id="review-decision-modal" tabindex="-1" aria-labelledby="reviewDecisionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="reviewDecisionModalLabel">Review decision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-review-decision-modal" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted mb-3">Mark this task complete or send it back for revision. If you
                            choose revision, select how much time is allowed to finish the work.</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="decision" id="rd_decision_complete"
                                value="complete" checked>
                            <label class="form-check-label" for="rd_decision_complete">Complete</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="decision" id="rd_decision_revision"
                                value="revision">
                            <label class="form-check-label" for="rd_decision_revision">Revision</label>
                        </div>
                        <div id="rd-revision-hours-wrap" class="d-none mb-0">
                            <label class="form-label" for="rd_revision_hours">Revision time allowance</label>
                            <select name="revision_hours" id="rd_revision_hours" class="form-select">
                                <option value="2">2 hours</option>
                                <option value="3">3 hours</option>
                                <option value="4">4 hours</option>
                            </select>

                            <div class="mt-3">
                                <label class="form-label" for="rd_revision_notes">Revision notes <span class="text-danger">*</span></label>
                                <textarea name="revision_notes" id="rd_revision_notes" class="form-control" rows="3"
                                    placeholder="Jelaskan revisi yang harus dikerjakan..." maxlength="2000"></textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label" for="rd_revision_links">Link referensi (opsional)</label>
                                <textarea name="revision_links" id="rd_revision_links" class="form-control" rows="2" maxlength="5000"
                                    placeholder="Satu link per baris (opsional)"></textarea>
                            </div>
                            @include('view.tasks.partials.tasks.photo-uploader-field', [
                                'inputId' => 'rd_revision_photos',
                                'previewId' => 'rd_revision_photo_preview',
                                'resetModalId' => 'review-decision-modal',
                                'label' => 'Gambar referensi (opsional)',
                            ])
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
