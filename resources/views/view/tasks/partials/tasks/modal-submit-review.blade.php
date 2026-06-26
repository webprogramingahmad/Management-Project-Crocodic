@if (in_array($role ?? Auth::user()->role->role, ['staff', 'director'], true))
    <div class="modal fade" id="submit-review-modal" tabindex="-1" aria-labelledby="submitReviewModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="submitReviewModalLabel">Hasil pengerjaan task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-submit-review" enctype="multipart/form-data">
                    <div class="modal-body">
                        <p class="small text-muted mb-3" id="submit-review-hint">
                            Lengkapi hasil kerja sebelum task dipindahkan ke Review.
                        </p>
                        <div class="mb-3">
                            <label class="form-label" for="submit_review_notes">Keterangan hasil <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="submit_review_notes" name="notes" rows="4" required
                                maxlength="5000" placeholder="Jelaskan hasil pengerjaan task..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="submit_review_links">Link (opsional)</label>
                            <textarea class="form-control" id="submit_review_links" name="links" rows="2" maxlength="5000"
                                placeholder="Satu link per baris (opsional)"></textarea>
                        </div>
                        @include('view.tasks.partials.tasks.photo-uploader-field', [
                            'inputId' => 'submit_review_photos',
                            'previewId' => 'submit_review_photo_preview',
                            'resetModalId' => 'submit-review-modal',
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submit-review-btn">Kirim ke Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
