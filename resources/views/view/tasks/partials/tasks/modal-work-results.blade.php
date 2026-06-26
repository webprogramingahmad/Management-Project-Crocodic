<div class="modal fade" id="task-work-results-modal" tabindex="-1" aria-labelledby="taskWorkResultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="taskWorkResultsLabel">Hasil kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2" id="task-work-results-body">
                <div class="text-center text-muted py-4" id="task-work-results-loading">Memuat...</div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .work-results-meta {
        border: 1px solid rgba(224, 224, 224, 0.9);
        border-radius: .75rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
        background: rgba(244, 244, 244, 0.35);
    }

    .work-results-meta-title {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: .75rem;
    }

    .work-results-meta-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .875rem;
        margin-bottom: .35rem;
    }

    .work-results-meta-row:last-child {
        margin-bottom: 0;
    }

    .work-results-meta-row .text-muted {
        min-width: 5.5rem;
    }

    .work-result-section {
        border: 1px solid rgba(224, 224, 224, 0.9);
        border-radius: .75rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .work-result-section:last-child {
        margin-bottom: 0;
    }

    .work-result-section-title {
        font-size: .9375rem;
        font-weight: 700;
        margin-bottom: .85rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid rgba(224, 224, 224, 0.7);
    }

    .work-result-subsection {
        margin-bottom: 1rem;
    }

    .work-result-subsection:last-child {
        margin-bottom: 0;
    }

    .work-result-subsection-label {
        font-size: .75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c757d;
        margin-bottom: .5rem;
    }

    .work-result-timing {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: .85rem;
    }

    .work-result-timing-item {
        display: inline-flex;
        flex-direction: column;
        gap: .15rem;
        padding: .45rem .75rem;
        border-radius: 9999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: .75rem;
        min-width: 7.5rem;
    }

    .work-result-timing-item--overdue {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .work-result-timing-item--overdue .work-result-timing-value {
        color: #b91c1c;
    }

    .work-result-timing-item--ok {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .work-result-timing-item--ok .work-result-timing-value {
        color: #15803d;
    }

    .work-result-timing-label {
        color: #64748b;
        font-weight: 500;
    }

    .work-result-timing-value {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        font-size: .8125rem;
    }

    .work-result-field {
        margin-bottom: .75rem;
    }

    .work-result-field:last-child {
        margin-bottom: 0;
    }

    .work-result-field-label {
        font-size: .8125rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: .35rem;
    }

    .work-result-field-value {
        font-size: .875rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .work-result-field-value a {
        color: #0d6efd;
        text-decoration: none;
    }

    .work-result-field-value a:hover {
        text-decoration: underline;
    }

    .work-result-photos {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .work-result-photos .modal-photo-thumb {
        width: 72px;
        height: 72px;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        flex-shrink: 0;
    }

    .work-result-photos .modal-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .work-result-allocation-hint {
        font-size: .8125rem;
        color: #6c757d;
        margin-bottom: .65rem;
    }

    html[data-theme="dark"] .work-results-meta {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(161, 161, 170, 0.35);
    }

    html[data-theme="dark"] .work-result-section {
        border-color: rgba(161, 161, 170, 0.35);
    }

    html[data-theme="dark"] .work-result-section-title {
        border-bottom-color: rgba(161, 161, 170, 0.25);
    }

    html[data-theme="dark"] .work-result-timing-item {
        background: rgba(255, 255, 255, 0.06);
        border-color: #3f3f46;
    }

    html[data-theme="dark"] .work-result-timing-item--overdue {
        background: rgba(220, 38, 38, 0.12);
        border-color: #7f1d1d;
    }

    html[data-theme="dark"] .work-result-timing-item--ok {
        background: rgba(22, 163, 74, 0.12);
        border-color: #166534;
    }

    html[data-theme="dark"] .work-result-photos .modal-photo-thumb {
        border-color: #3f3f46;
    }
</style>
