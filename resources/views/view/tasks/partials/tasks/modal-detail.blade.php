@php
    $detailRole = Auth::user()->role->role;
    $photoDestroyUrl = match ($detailRole) {
        'staff' => route('staff.task.photo.destroy', ['photo' => '__ID__']),
        'director' => route('director.task.photo.destroy', ['photo' => '__ID__']),
        default => null,
    };
@endphp

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

    <!-- Time Tracking -->
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

    <!-- Keterangan / tautan (opsional) -->
    <div class="col-12 info-row d-flex align-items-start mb-0 modal-task-description-row d-none">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-link-45deg"></i>
            <span>Notes</span>
        </div>
        <div class="col-7 modal-task-description small text-break"></div>
    </div>

    <!-- Catatan revisi dari director (opsional) -->
    <div class="col-12 info-row d-flex align-items-start mb-2 modal-revision-note-row d-none">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Revision notes</span>
        </div>
        <div class="col-7 modal-revision-note small text-break"></div>
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

    <!-- Work Evidence -->
    <div class="col-12 info-row d-flex align-items-start mt-2 modal-task-photos-row d-none">
        <div class="col-5 d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-images"></i>
            <span>Work Evidence</span>
        </div>
        <div class="col-7">
            <div class="modal-task-photos d-flex flex-wrap gap-2"></div>
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

<style>
    .modal-task-photos .modal-photo-thumb {
        position: relative;
        width: 64px;
        height: 64px;
        border-radius: .4rem;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .modal-task-photos .modal-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .modal-task-photos .modal-photo-remove {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 18px;
        height: 18px;
        padding: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
    }

    html[data-theme="dark"] .modal-task-photos .modal-photo-thumb {
        border-color: rgba(161, 161, 170, 0.35);
    }
</style>

<script>
    (function () {
        var modalEl = document.getElementById('detail-task');
        if (!modalEl || modalEl.dataset.photosBound === '1') return;
        modalEl.dataset.photosBound = '1';

        var currentUserId = @json((string) Auth::id());
        var destroyUrlTpl = @json($photoDestroyUrl);

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        }

        modalEl.addEventListener('show.bs.modal', function (ev) {
            var card = ev.relatedTarget || window.__lastTaskCardForEdit || null;
            var row = modalEl.querySelector('.modal-task-photos-row');
            var wrap = modalEl.querySelector('.modal-task-photos');
            if (!row || !wrap) return;

            wrap.innerHTML = '';

            var photos = [];
            var ownerId = '';
            if (card) {
                ownerId = card.getAttribute('data-task-owner-id') || '';
                try {
                    photos = JSON.parse(card.getAttribute('data-task-photos-json') || '[]') || [];
                } catch (e) {
                    photos = [];
                }
            }

            if (!photos.length) {
                row.classList.add('d-none');
                return;
            }
            row.classList.remove('d-none');

            var canDelete = !!destroyUrlTpl && ownerId && String(ownerId) === String(currentUserId);

            photos.forEach(function (p) {
                var thumb = document.createElement('div');
                thumb.className = 'modal-photo-thumb';

                var link = document.createElement('a');
                link.href = p.url;
                link.target = '_blank';
                link.rel = 'noopener';

                var img = document.createElement('img');
                img.src = p.url;
                img.alt = 'Work evidence';
                link.appendChild(img);
                thumb.appendChild(link);

                if (canDelete) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'modal-photo-remove btn btn-danger';
                    btn.innerHTML = '<i class="bi bi-x"></i>';
                    btn.addEventListener('click', function () {
                        if (!confirm('Hapus foto ini?')) return;
                        var url = destroyUrlTpl.replace('__ID__', p.id);
                        var fd = new FormData();
                        fd.append('_method', 'DELETE');
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: fd
                        }).then(function (r) {
                            if (!r.ok) throw new Error('failed');
                            thumb.remove();
                            if (card) {
                                try {
                                    var arr = (JSON.parse(card.getAttribute('data-task-photos-json') || '[]') || [])
                                        .filter(function (x) { return String(x.id) !== String(p.id); });
                                    card.setAttribute('data-task-photos-json', JSON.stringify(arr));
                                } catch (e) {}
                            }
                            if (!wrap.children.length) row.classList.add('d-none');
                        }).catch(function () {
                            alert('Gagal menghapus foto.');
                        });
                    });
                    thumb.appendChild(btn);
                }

                wrap.appendChild(thumb);
            });
        });
    })();
</script>